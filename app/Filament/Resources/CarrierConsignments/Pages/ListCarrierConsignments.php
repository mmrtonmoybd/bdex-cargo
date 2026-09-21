<?php

namespace App\Filament\Resources\CarrierConsignments\Pages;

use App\Filament\Resources\CarrierConsignments\CarrierConsignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarrierConsignments extends ListRecords
{
    protected static string $resource = CarrierConsignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
