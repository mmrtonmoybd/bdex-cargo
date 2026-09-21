<?php

declare(strict_types=1);

namespace App\Filament\Portal\Resources\Shipments\Pages;

use App\Filament\Portal\Resources\Shipments\ShipmentResource;
use Filament\Resources\Pages\ListRecords;

class ListShipments extends ListRecords
{
    protected static string $resource = ShipmentResource::class;

    public function getTitle(): string
    {
        return 'My shipments';
    }
}
