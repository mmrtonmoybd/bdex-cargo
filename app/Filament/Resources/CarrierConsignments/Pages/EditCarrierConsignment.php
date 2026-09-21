<?php

namespace App\Filament\Resources\CarrierConsignments\Pages;

use App\Filament\Resources\CarrierConsignments\CarrierConsignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarrierConsignment extends EditRecord
{
    protected static string $resource = CarrierConsignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
