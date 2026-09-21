<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where an FX rate row came from.
 */
enum FxRateSourceType: string implements HasLabel
{
    case Manual = 'manual';
    case Api = 'api';

    public function getLabel(): string
    {
        return match ($this) {
            self::Manual => 'Entered by staff',
            self::Api => 'Fetched from API',
        };
    }
}
