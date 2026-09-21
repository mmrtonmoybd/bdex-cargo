<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConfigEntries\Pages;

use App\Filament\Resources\ConfigEntries\ConfigEntryResource;
use App\Models\ConfigEntry;
use App\Services\Config\DynamicConfig;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConfigEntry extends EditRecord
{
    protected static string $resource = ConfigEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /** Show the decrypted value so staff edit what they can actually read. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var ConfigEntry $record */
        $record = $this->getRecord();

        $data['value'] = $record->plainValue();

        return $data;
    }

    /** Re-encrypt on the way back in, honouring a freshly toggled secret flag. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $entry = new ConfigEntry(['is_secret' => (bool) ($data['is_secret'] ?? false)]);
        $entry->fillPlainValue($data['value'] ?? null);

        $data['value'] = $entry->value;

        return $data;
    }

    protected function afterSave(): void
    {
        DynamicConfig::forget($this->getRecord()->key);
    }
}
