<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Takes a payment and keeps the order's status honest.
 *
 * Every attempt is written down, successful or not, and the order's payment
 * status is derived from what actually cleared rather than set by hand — so
 * "paid" on an order and the money received can be reconciled.
 */
final class PaymentProcessor
{
    public function __construct(private readonly PaymentGatewayFactory $gateways) {}

    public function charge(Order $order, ?float $amount = null): Payment
    {
        $driver = $this->gateways->forCurrency($order->payment_currency);
        $amount ??= max(0, (float) $order->total_cost_aud - (float) $order->paid_amount);

        try {
            $result = $driver->charge($order, $amount);
        } catch (PaymentException $exception) {
            return $this->record($order, $driver, $amount, new PaymentResult(
                status: PaymentStatus::Failed,
                failureReason: $exception->getMessage(),
            ));
        }

        return $this->record($order, $driver, $amount, $result);
    }

    /** Re-asks the gateway what happened, and settles the order if it cleared. */
    public function verify(Payment $payment): Payment
    {
        $driver = $this->gateways->forCurrency($payment->currency);

        if (blank($payment->gateway_reference)) {
            return $payment;
        }

        try {
            $result = $driver->verify($payment->gateway_reference);
        } catch (PaymentException $exception) {
            $payment->forceFill(['failure_reason' => $exception->getMessage()])->save();

            return $payment;
        }

        return $this->apply($payment, $result);
    }

    /**
     * Staff confirming money that arrived outside any gateway — a bank transfer,
     * or cash at the counter. The commonest case, and a first-class one.
     */
    public function recordManualPayment(Order $order, float $amount, ?int $userId = null, ?string $reference = null): Payment
    {
        return DB::transaction(function () use ($order, $amount, $userId, $reference): Payment {
            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'driver_class' => Drivers\ManualPaymentDriver::class,
                'driver_name' => 'manual',
                'amount' => $amount,
                'currency' => $order->payment_currency,
                'status' => PaymentStatus::Paid,
                'gateway_reference' => $reference,
                'recorded_by' => $userId,
                'paid_at' => now(),
            ]);

            $this->settle($order);

            return $payment;
        });
    }

    private function record(Order $order, PaymentGatewayDriver $driver, float $amount, PaymentResult $result): Payment
    {
        return DB::transaction(function () use ($order, $driver, $amount, $result): Payment {
            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'driver_class' => $driver::class,
                'driver_name' => $driver->name(),
                'amount' => $amount,
                'currency' => $order->payment_currency,
                'status' => $result->status,
                'gateway_reference' => $result->gatewayReference,
                'redirect_url' => $result->redirectUrl,
                'raw_payload' => $result->rawPayload,
                'failure_reason' => $result->failureReason,
                'paid_at' => $result->succeeded() ? now() : null,
            ]);

            $order->forceFill(['payment_gateway_driver' => $driver::class])->save();

            if ($result->succeeded()) {
                $this->settle($order);
            }

            return $payment;
        });
    }

    private function apply(Payment $payment, PaymentResult $result): Payment
    {
        $payment->forceFill([
            'status' => $result->status,
            'raw_payload' => $result->rawPayload,
            'paid_at' => $result->succeeded() ? ($payment->paid_at ?? now()) : null,
        ])->save();

        if ($result->succeeded()) {
            $this->settle($payment->order);
        }

        return $payment;
    }

    /** Recomputes what the order owes from the payments that actually cleared. */
    private function settle(Order $order): void
    {
        $paid = (float) $order->payments()
            ->where('status', PaymentStatus::Paid->value)
            ->sum('amount');

        $total = (float) $order->total_cost_aud;

        $order->forceFill([
            'paid_amount' => $paid,
            'payment_status' => match (true) {
                $paid <= 0.0 => PaymentStatus::Pending,
                $paid + 0.01 >= $total => PaymentStatus::Paid,
                default => PaymentStatus::PartiallyPaid,
            },
        ])->save();
    }
}
