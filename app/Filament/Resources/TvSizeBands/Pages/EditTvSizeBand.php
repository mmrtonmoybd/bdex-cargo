<?php

namespace App\Filament\Resources\TvSizeBands\Pages;

use App\Filament\Resources\TvSizeBands\TvSizeBandResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTvSizeBand extends EditRecord
{
    protected static string $resource = TvSizeBandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
