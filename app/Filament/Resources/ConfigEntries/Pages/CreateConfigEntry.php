<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConfigEntries\Pages;

use App\Filament\Resources\ConfigEntries\ConfigEntryResource;
use App\Models\ConfigEntry;
use App\Services\Config\DynamicConfig;
use Filament\Resources\Pages\CreateRecord;

class CreateConfigEntry extends CreateRecord
{
    protected static string $resource = ConfigEntryResource::class;

    /** Encrypt before the row is written, so a secret is never persisted in the clear. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $entry = new ConfigEntry(['is_secret' => (bool) ($data['is_secret'] ?? false)]);
        $entry->fillPlainValue($data['value'] ?? null);

        $data['value'] = $entry->value;

        return $data;
    }

    protected function afterCreate(): void
    {
        DynamicConfig::forget($this->getRecord()->key);
    }
}
