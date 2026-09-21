<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConfigEntries;

use App\Filament\Resources\ConfigEntries\Pages\CreateConfigEntry;
use App\Filament\Resources\ConfigEntries\Pages\EditConfigEntry;
use App\Filament\Resources\ConfigEntries\Pages\ListConfigEntries;
use App\Filament\Resources\ConfigEntries\Schemas\ConfigEntryForm;
use App\Filament\Resources\ConfigEntries\Tables\ConfigEntriesTable;
use App\Models\ConfigEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ConfigEntryResource extends Resource
{
    protected static ?string $model = ConfigEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Schema $schema): Schema
    {
        return ConfigEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConfigEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConfigEntries::route('/'),
            'create' => CreateConfigEntry::route('/create'),
            'edit' => EditConfigEntry::route('/{record}/edit'),
        ];
    }
}
