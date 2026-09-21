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
 * SSLCommerz — the Bangladesh side, which fronts bKash, Nagad and the cards in
 * one hosted checkout.
 */
final class SslCommerzPaymentDriver implements PaymentGatewayDriver
{
    public function __construct(private readonly HttpFactory $http) {}

    public function charge(Order $order, float $amount): PaymentResult
    {
        $storeId = (string) DynamicConfig::get('payment.sslcommerz.store_id');
        $password = (string) DynamicConfig::get('payment.sslcommerz.store_password');
        $base = (string) DynamicConfig::get('payment.sslcommerz.base_url', 'https://sandbox.sslcommerz.com');

        if ($storeId === '' || $password === '') {
            throw new PaymentException('SSLCommerz is selected for Bangladesh payments but its store credentials are not set under System → Configuration.');
        }

        try {
            $response = $this->http
                ->asForm()
                ->timeout(20)
                ->post(rtrim($base, '/').'/gwprocess/v4/api.php', [
                    'store_id' => $storeId,
                    'store_passwd' => $password,
                    'total_amount' => round($amount, 2),
                    'currency' => $this->currency(),
                    'tran_id' => $order->bdex_tracking_number,
                    'success_url' => url('/?paid='.$order->bdex_tracking_number),
                    'fail_url' => url('/#booking'),
                    'cancel_url' => url('/#booking'),
                    'cus_name' => $order->sender_name,
                    'cus_phone' => $order->sender_phone,
                    'product_name' => 'Shipment '.$order->bdex_tracking_number,
                    'product_category' => 'Courier',
                    'product_profile' => 'general',
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new PaymentException("SSLCommerz refused the payment: {$exception->getMessage()}", previous: $exception);
        }

        if ($response->json('status') !== 'SUCCESS') {
            throw new PaymentException('SSLCommerz declined the session: '.(string) $response->json('failedreason'));
        }

        return new PaymentResult(
            status: PaymentStatus::Pending,
            gatewayReference: (string) $response->json('sessionkey'),
            redirectUrl: (string) $response->json('GatewayPageURL'),
            rawPayload: (array) $response->json(),
        );
    }

    public function verify(string $gatewayReference): PaymentResult
    {
        $storeId = (string) DynamicConfig::get('payment.sslcommerz.store_id');
        $password = (string) DynamicConfig::get('payment.sslcommerz.store_password');
        $base = (string) DynamicConfig::get('payment.sslcommerz.base_url', 'https://sandbox.sslcommerz.com');

        try {
            $response = $this->http
                ->timeout(20)
                ->get(rtrim($base, '/').'/validator/api/merchantTransIDvalidationAPI.php', [
                    'sessionkey' => $gatewayReference,
                    'store_id' => $storeId,
                    'store_passwd' => $password,
                    'format' => 'json',
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new PaymentException("SSLCommerz could not be reached: {$exception->getMessage()}", previous: $exception);
        }

        $status = (string) $response->json('element.0.status');

        return new PaymentResult(
            status: in_array($status, ['VALID', 'VALIDATED'], true) ? PaymentStatus::Paid : PaymentStatus::Pending,
            gatewayReference: $gatewayReference,
            rawPayload: (array) $response->json(),
        );
    }

    public function name(): string
    {
        return 'sslcommerz';
    }

    public function currency(): string
    {
        return 'BDT';
    }
}
