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

/** bKash Checkout (tokenised). */
final class BkashPaymentDriver implements PaymentGatewayDriver
{
    public function __construct(private readonly HttpFactory $http) {}

    public function charge(Order $order, float $amount): PaymentResult
    {
        $base = (string) DynamicConfig::get('payment.bkash.base_url', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta');

        try {
            $response = $this->http
                ->withHeaders($this->headers())
                ->asJson()
                ->timeout(20)
                ->post(rtrim($base, '/').'/tokenized/checkout/create', [
                    'mode' => '0011',
                    'payerReference' => $order->sender_phone,
                    'callbackURL' => url('/?paid='.$order->bdex_tracking_number),
                    'amount' => (string) round($amount, 2),
                    'currency' => $this->currency(),
                    'intent' => 'sale',
                    'merchantInvoiceNumber' => $order->bdex_tracking_number,
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new PaymentException("bKash refused the payment: {$exception->getMessage()}", previous: $exception);
        }

        return new PaymentResult(
            status: PaymentStatus::Pending,
            gatewayReference: (string) $response->json('paymentID'),
            redirectUrl: (string) $response->json('bkashURL'),
            rawPayload: (array) $response->json(),
        );
    }

    public function verify(string $gatewayReference): PaymentResult
    {
        $base = (string) DynamicConfig::get('payment.bkash.base_url', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta');

        try {
            $response = $this->http
                ->withHeaders($this->headers())
                ->asJson()
                ->timeout(20)
                ->post(rtrim($base, '/').'/tokenized/checkout/payment/status', ['paymentID' => $gatewayReference])
                ->throw();
        } catch (Throwable $exception) {
            throw new PaymentException("bKash could not be reached: {$exception->getMessage()}", previous: $exception);
        }

        return new PaymentResult(
            status: $response->json('transactionStatus') === 'Completed' ? PaymentStatus::Paid : PaymentStatus::Pending,
            gatewayReference: $gatewayReference,
            rawPayload: (array) $response->json(),
        );
    }

    /** @return array<string, string> */
    private function headers(): array
    {
        $token = (string) DynamicConfig::get('payment.bkash.id_token');
        $appKey = (string) DynamicConfig::get('payment.bkash.app_key');

        if ($token === '' || $appKey === '') {
            throw new PaymentException('bKash is selected for Bangladesh payments but its credentials are not set under System → Configuration.');
        }

        return ['Authorization' => $token, 'X-APP-Key' => $appKey];
    }

    public function name(): string
    {
        return 'bkash';
    }

    public function currency(): string
    {
        return 'BDT';
    }
}
