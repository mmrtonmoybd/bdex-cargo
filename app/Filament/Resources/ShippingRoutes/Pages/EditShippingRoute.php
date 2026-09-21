<?php

namespace App\Filament\Resources\ShippingRoutes\Pages;

use App\Filament\Resources\ShippingRoutes\ShippingRouteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShippingRoute extends EditRecord
{
    protected static string $resource = ShippingRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
