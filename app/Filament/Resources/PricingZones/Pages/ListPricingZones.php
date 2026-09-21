<?php

namespace App\Filament\Resources\PricingZones\Pages;

use App\Filament\Resources\PricingZones\PricingZoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPricingZones extends ListRecords
{
    protected static string $resource = PricingZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
