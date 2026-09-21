<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** How the parcel reaches us. */
enum FulfilmentMethod: string implements HasLabel
{
    case CourierPickup = 'courier_pickup';
    case AustraliaPostDropOff = 'auspost_dropoff';
    case OfficeDropOff = 'office_dropoff';

    public function getLabel(): string
    {
        return match ($this) {
            self::CourierPickup => 'Courier pickup',
            self::AustraliaPostDropOff => 'Australia Post drop-off',
            self::OfficeDropOff => 'Drop off at our office',
        };
    }
}
