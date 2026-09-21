<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Enums\PaymentStatus;

/**
 * What a gateway said when asked to take money.
 *
 * `redirectUrl` is set when the customer has to finish the payment somewhere
 * else — which is how bKash, Nagad and SSLCommerz all work.
 */
final readonly class PaymentResult
{
    /** @param array<string, mixed> $rawPayload */
    public function __construct(
        public PaymentStatus $status,
        public ?string $gatewayReference = null,
        public ?string $redirectUrl = null,
        public ?string $failureReason = null,
        public array $rawPayload = [],
    ) {}

    public function succeeded(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }
}
