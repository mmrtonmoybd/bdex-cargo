<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Which shipments a weight tier's price-per-kg applies to.
 *
 * The BD→AU table is garment-only; the "*For Garment items only" footnote on the
 * published rate card is this column, not a comment.
 */
enum WeightTierScope: string implements HasLabel
{
    case GarmentOnly = 'garment_only';
    case All = 'all';

    public function getLabel(): string
    {
        return match ($this) {
            self::GarmentOnly => 'Garment items only',
            self::All => 'All items',
        };
    }
}
