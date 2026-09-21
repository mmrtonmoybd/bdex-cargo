<?php

namespace App\Filament\Resources\PricingZones\Pages;

use App\Filament\Resources\PricingZones\PricingZoneResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPricingZone extends EditRecord
{
    protected static string $resource = PricingZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
