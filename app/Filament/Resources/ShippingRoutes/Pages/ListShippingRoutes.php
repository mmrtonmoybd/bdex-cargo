<?php

namespace App\Filament\Resources\ShippingRoutes\Pages;

use App\Filament\Resources\ShippingRoutes\ShippingRouteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShippingRoutes extends ListRecords
{
    protected static string $resource = ShippingRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
