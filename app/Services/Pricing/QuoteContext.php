<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Enums\ConditionField;

/**
 * The closed set of facts a surcharge rule may test.
 *
 * Every {@see ConditionField} case resolves here, so a rule an admin builds in
 * Filament can never reference something the evaluator has no value for.
 */
final readonly class QuoteContext
{
    public function __construct(
        public float $declaredValue = 0.0,
        public float $boxWeightKg = 0.0,
        public float $totalWeightKg = 0.0,
        public bool $isNonGarment = false,
        public int $boxCount = 0,
        public int $itemCount = 0,
    ) {}

    public function value(ConditionField $field): float|bool|int
    {
        return match ($field) {
            ConditionField::DeclaredValue => $this->declaredValue,
            ConditionField::BoxWeightKg => $this->boxWeightKg,
            ConditionField::TotalWeightKg => $this->totalWeightKg,
            ConditionField::IsNonGarment => $this->isNonGarment,
            ConditionField::BoxCount => $this->boxCount,
            ConditionField::ItemCount => $this->itemCount,
        };
    }

    /**
     * A context describing one line rather than the whole shipment.
     *
     * Used for category-scoped rules such as "electronics declared over $100 carry
     * an extra fee per item", where the comparison is against that line's own
     * declared value, not the shipment's highest.
     */
    public function forItem(QuoteItem $item): self
    {
        return new self(
            declaredValue: $item->declaredValue,
            boxWeightKg: $this->boxWeightKg,
            totalWeightKg: $item->totalWeightKg(),
            isNonGarment: ! $item->category->is_garment,
            boxCount: $this->boxCount,
            itemCount: $item->quantity,
        );
    }
}
