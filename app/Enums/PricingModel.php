<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How an item category's base price is derived.
 *
 * Every model here is data-driven: the numbers live in
 * `item_category_zone_rates` or on the category row itself, never in PHP.
 */
enum PricingModel: string implements HasLabel
{
    /** Rate per kg, looked up per pricing zone (the AU→BD general case). */
    case PerKgByZone = 'per_kg_by_zone';

    /** One fixed amount for the whole line, regardless of weight or quantity. */
    case FlatFee = 'flat_fee';

    /** One fixed amount charged once per unit of quantity. */
    case FlatFeePerItem = 'flat_fee_per_item';

    /** Rate per kg, identical across every zone on the route. */
    case PerKgFlat = 'per_kg_flat';

    public function getLabel(): string
    {
        return match ($this) {
            self::PerKgByZone => 'Per kg (rate varies by zone)',
            self::FlatFee => 'Flat fee (per line)',
            self::FlatFeePerItem => 'Flat fee (per item)',
            self::PerKgFlat => 'Per kg (same rate all zones)',
        };
    }

    /** Whether this model reads rates from `item_category_zone_rates`. */
    public function usesZoneRates(): bool
    {
        return $this === self::PerKgByZone;
    }

    /** Whether this model needs `flat_fee_amount` on the category row. */
    public function usesFlatFeeAmount(): bool
    {
        return in_array($this, [self::FlatFee, self::FlatFeePerItem, self::PerKgFlat], true);
    }
}
