<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Config\DynamicConfig;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentGatewayDriver;
use App\Services\Payments\PaymentResult;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

/**
 * Stripe Checkout — the Australian side.
 *
 * Talks to the REST API directly rather than pulling in the SDK: one HTTP call
 * each way, and the credential is read per request from config_entries so a
 * rotated key takes effect immediately.
 */
final class StripePaymentDriver implements PaymentGatewayDriver
{
    public function __construct(private readonly HttpFactory $http) {}

    public function charge(Order $order, float $amount): PaymentResult
    {
        $secret = (string) DynamicConfig::get('payment.stripe.secret_key');

        if ($secret === '') {
            throw new PaymentException('Stripe is selected for Australian payments but its secret key is not set under System → Configuration.');
        }

        try {
            $response = $this->http
                ->withToken($secret)
                ->asForm()
                ->timeout(20)
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'mode' => 'payment',
                    'success_url' => url('/?paid='.$order->bdex_tracking_number),
                    'cancel_url' => url('/#booking'),
                    'client_reference_id' => $order->bdex_tracking_number,
                    'line_items[0][quantity]' => 1,
                    'line_items[0][price_data][currency]' => strtolower($this->currency()),
                    // Stripe works in the smallest unit, so dollars become cents.
                    'line_items[0][price_data][unit_amount]' => (int) round($amount * 100),
                    'line_items[0][price_data][product_data][name]' => 'Shipment '.$order->bdex_tracking_number,
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new PaymentException("Stripe refused the payment: {$exception->getMessage()}", previous: $exception);
        }

        return new PaymentResult(
            status: PaymentStatus::Pending,
            gatewayReference: (string) $response->json('id'),
            redirectUrl: (string) $response->json('url'),
            rawPayload: (array) $response->json(),
        );
    }

    public function verify(string $gatewayReference): PaymentResult
    {
        $secret = (string) DynamicConfig::get('payment.stripe.secret_key');

        try {
            $response = $this->http
                ->withToken($secret)
                ->timeout(20)
                ->get("https://api.stripe.com/v1/checkout/sessions/{$gatewayReference}")
                ->throw();
        } catch (Throwable $exception) {
            throw new PaymentException("Stripe could not be reached: {$exception->getMessage()}", previous: $exception);
        }

        return new PaymentResult(
            status: $response->json('payment_status') === 'paid' ? PaymentStatus::Paid : PaymentStatus::Pending,
            gatewayReference: $gatewayReference,
            rawPayload: (array) $response->json(),
        );
    }

    public function name(): string
    {
        return 'stripe';
    }

    public function currency(): string
    {
        return 'AUD';
    }
}
