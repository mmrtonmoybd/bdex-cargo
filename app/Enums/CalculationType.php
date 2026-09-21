<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How a surcharge rule turns its `amount` into money.
 */
enum CalculationType: string implements HasLabel
{
    /** `amount` is charged once for the shipment. */
    case Flat = 'flat';

    /** `amount` is a percentage of the running subtotal. */
    case Percentage = 'percentage';

    /** `amount` is multiplied by the shipment's chargeable weight. */
    case PerKg = 'per_kg';

    /** `amount` is multiplied by the number of qualifying items. */
    case PerItem = 'per_item';

    public function getLabel(): string
    {
        return match ($this) {
            self::Flat => 'Flat amount',
            self::Percentage => 'Percentage of subtotal',
            self::PerKg => 'Per kg',
            self::PerItem => 'Per item',
        };
    }
}
