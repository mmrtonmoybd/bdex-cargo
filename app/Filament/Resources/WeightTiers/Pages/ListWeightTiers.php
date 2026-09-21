<?php

namespace App\Filament\Resources\WeightTiers\Pages;

use App\Filament\Resources\WeightTiers\WeightTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWeightTiers extends ListRecords
{
    protected static string $resource = WeightTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
