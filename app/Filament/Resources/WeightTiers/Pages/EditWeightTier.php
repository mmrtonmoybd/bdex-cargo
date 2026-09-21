<?php

namespace App\Filament\Resources\WeightTiers\Pages;

use App\Filament\Resources\WeightTiers\WeightTierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWeightTier extends EditRecord
{
    protected static string $resource = WeightTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
