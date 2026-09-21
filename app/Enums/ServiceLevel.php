<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ServiceLevel: string implements HasLabel
{
    case Standard = 'standard';
    case Express = 'express';

    public function getLabel(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Express => 'Express',
        };
    }
}
