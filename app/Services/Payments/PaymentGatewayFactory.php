<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Services\Config\DynamicConfig;
use App\Services\Payments\Drivers\BkashPaymentDriver;
use App\Services\Payments\Drivers\ManualPaymentDriver;
use App\Services\Payments\Drivers\SslCommerzPaymentDriver;
use App\Services\Payments\Drivers\StripePaymentDriver;
use Illuminate\Contracts\Container\Container;

/**
 * Resolves the gateway for a side of the business.
 *
 * Australia and Bangladesh are separate selections because they are separate
 * payment worlds, and a customer may settle on either side. Both default to
 * manual confirmation, which is how BD-EX already works — so an unconfigured
 * system still takes bookings and money, just with a human in the loop.
 */
final class PaymentGatewayFactory
{
    /** @var array<string, class-string<PaymentGatewayDriver>> */
    private const DRIVERS = [
        'manual' => ManualPaymentDriver::class,
        'stripe' => StripePaymentDriver::class,
        'sslcommerz' => SslCommerzPaymentDriver::class,
        'bkash' => BkashPaymentDriver::class,
    ];

    public function __construct(private readonly Container $container) {}

    public function forCurrency(string $currency): PaymentGatewayDriver
    {
        return strtoupper($currency) === 'BDT' ? $this->bangladesh() : $this->australia();
    }

    public function australia(): PaymentGatewayDriver
    {
        return $this->resolve((string) DynamicConfig::get('payment.au.driver', 'manual'));
    }

    public function bangladesh(): PaymentGatewayDriver
    {
        return $this->resolve((string) DynamicConfig::get('payment.bd.driver', 'manual'));
    }

    /** @return array<string, string> */
    public static function australianOptions(): array
    {
        return ['manual' => 'Manual (staff confirm)', 'stripe' => 'Stripe'];
    }

    /** @return array<string, string> */
    public static function bangladeshOptions(): array
    {
        return [
            'manual' => 'Manual (staff confirm)',
            'sslcommerz' => 'SSLCommerz',
            'bkash' => 'bKash',
        ];
    }

    private function resolve(string $selected): PaymentGatewayDriver
    {
        // An unrecognised selection falls back to manual rather than throwing: a
        // misconfigured gateway must not stop money being recorded.
        return $this->container->make(self::DRIVERS[$selected] ?? ManualPaymentDriver::class);
    }
}
