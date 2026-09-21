<?php

namespace App\Filament\Resources\ConfigEntries\Pages;

use App\Filament\Resources\ConfigEntries\ConfigEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConfigEntries extends ListRecords
{
    protected static string $resource = ConfigEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
