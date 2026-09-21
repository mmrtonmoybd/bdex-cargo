<?php

declare(strict_types=1);

namespace App\Services\Pricing;

/**
 * One row of the itemised quote shown to the customer.
 *
 * `sourceCode` traces the amount back to the rule that produced it — a
 * surcharge_rules.code, an item category name, or a weight tier — so "no
 * surprises" pricing is a property of the data, not a promise in the copy.
 */
final readonly class QuoteLineItem
{
    public function __construct(
        public string $label,
        public float $amount,
        public string $sourceType,
        public ?string $sourceCode = null,
        public ?string $detail = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'amount' => round($this->amount, 2),
            'source_type' => $this->sourceType,
            'source_code' => $this->sourceCode,
            'detail' => $this->detail,
        ];
    }
}
