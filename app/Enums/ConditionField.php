<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The quote-context facts a surcharge rule is allowed to test.
 *
 * Closed on purpose: an admin picks from this list in Filament, so a rule can
 * never reference a fact the evaluator has no value for. Adding a new fact is a
 * deliberate two-line change here plus a property on QuoteContext.
 */
enum ConditionField: string implements HasLabel
{
    /** Highest single declared item value on the shipment, in AUD. */
    case DeclaredValue = 'declared_value';

    /** Lightest box on the shipment, in kg (drives the AusPost under-15kg fee). */
    case BoxWeightKg = 'box_weight_kg';

    /** Total chargeable weight of the shipment, in kg. */
    case TotalWeightKg = 'total_weight_kg';

    /** True when the shipment contains anything that is not a garment. */
    case IsNonGarment = 'is_non_garment';

    /** Number of boxes on the shipment. */
    case BoxCount = 'box_count';

    /** Number of individual items on the shipment. */
    case ItemCount = 'item_count';

    public function getLabel(): string
    {
        return match ($this) {
            self::DeclaredValue => 'Highest declared item value (AUD)',
            self::BoxWeightKg => 'Lightest box weight (kg)',
            self::TotalWeightKg => 'Total shipment weight (kg)',
            self::IsNonGarment => 'Contains non-garment items',
            self::BoxCount => 'Number of boxes',
            self::ItemCount => 'Number of items',
        };
    }

    /** Whether the field holds a boolean rather than a number. */
    public function isBoolean(): bool
    {
        return $this === self::IsNonGarment;
    }
}
