<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Models\ItemCategory;

/**
 * One line a customer wants to send, as the pricing engine sees it.
 *
 * Pure input: no Eloquent writes, no request state. The booking wizard, the
 * public calculator and the tests all build these the same way.
 */
final readonly class QuoteItem
{
    public function __construct(
        public ItemCategory $category,
        public int $quantity = 1,
        public float $weightKg = 0.0,
        public float $declaredValue = 0.0,
    ) {}

    /** Weight the whole line contributes to the shipment. */
    public function totalWeightKg(): float
    {
        return $this->weightKg;
    }
}
