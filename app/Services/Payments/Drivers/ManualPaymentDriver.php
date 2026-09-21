<?php

declare(strict_types=1);

namespace App\Services\Payments\Drivers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Payments\PaymentGatewayDriver;
use App\Services\Payments\PaymentResult;

/**
 * No gateway: staff confirm the money arrived.
 *
 * The default, and not a placeholder — BD-EX already takes bank transfers and
 * cash on both sides, and a shipment must be bookable whether or not an online
 * gateway is configured.
 */
final class ManualPaymentDriver implements PaymentGatewayDriver
{
    public function charge(Order $order, float $amount): PaymentResult
    {
        return new PaymentResult(
            status: PaymentStatus::Pending,
            gatewayReference: 'MANUAL-'.$order->bdex_tracking_number,
            failureReason: null,
        );
    }

    public function verify(string $gatewayReference): PaymentResult
    {
        // Only a human knows whether a bank transfer landed.
        return new PaymentResult(status: PaymentStatus::Pending, gatewayReference: $gatewayReference);
    }

    public function name(): string
    {
        return 'manual';
    }

    public function currency(): string
    {
        return 'AUD';
    }
}
