<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Order;

/**
 * Taking a payment, without naming a vendor.
 *
 * Which gateway is live per side of the business is a config_entries value
 * (payment.au.driver, payment.bd.driver), so moving from one provider to another
 * — or adding a second for Bangladesh — is an edit on the Configuration page.
 * A provider nobody has coded against still needs one class implementing this,
 * which is the same honest boundary the carriers have.
 */
interface PaymentGatewayDriver
{
    /**
     * Start a payment. May complete immediately, or hand back a redirect.
     *
     * @throws PaymentException
     */
    public function charge(Order $order, float $amount): PaymentResult;

    /**
     * Ask the gateway what became of a payment we started.
     *
     * @throws PaymentException
     */
    public function verify(string $gatewayReference): PaymentResult;

    public function name(): string;

    /** The currency this gateway settles in. */
    public function currency(): string;
}
