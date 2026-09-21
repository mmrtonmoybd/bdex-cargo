<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Where a status entry came from, so a disputed timeline can be audited. */
enum StatusSource: string implements HasLabel
{
    case System = 'system';
    case CarrierSync = 'carrier_sync';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::System => 'System',
            self::CarrierSync => 'Carrier sync',
            self::Manual => 'Entered by staff',
        };
    }
}
