<?php

declare(strict_types=1);

namespace App\Filament\Resources\TvSizeBands;

use App\Filament\Resources\TvSizeBands\Pages\CreateTvSizeBand;
use App\Filament\Resources\TvSizeBands\Pages\EditTvSizeBand;
use App\Filament\Resources\TvSizeBands\Pages\ListTvSizeBands;
use App\Filament\Resources\TvSizeBands\Schemas\TvSizeBandForm;
use App\Filament\Resources\TvSizeBands\Tables\TvSizeBandsTable;
use App\Models\TvSizeBand;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TvSizeBandResource extends Resource
{
    protected static ?string $model = TvSizeBand::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTv;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'size_label';

    public static function form(Schema $schema): Schema
    {
        return TvSizeBandForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TvSizeBandsTable::configure($table);
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
            'index' => ListTvSizeBands::route('/'),
            'create' => CreateTvSizeBand::route('/create'),
            'edit' => EditTvSizeBand::route('/{record}/edit'),
        ];
    }
}
