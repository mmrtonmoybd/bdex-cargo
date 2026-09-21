<?php

namespace App\Filament\Resources\TvSizeBands\Pages;

use App\Filament\Resources\TvSizeBands\TvSizeBandResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTvSizeBands extends ListRecords
{
    protected static string $resource = TvSizeBandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
