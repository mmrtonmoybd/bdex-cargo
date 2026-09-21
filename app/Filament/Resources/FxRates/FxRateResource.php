<?php

declare(strict_types=1);

namespace App\Filament\Resources\FxRates;

use App\Filament\Resources\FxRates\Pages\CreateFxRate;
use App\Filament\Resources\FxRates\Pages\EditFxRate;
use App\Filament\Resources\FxRates\Pages\ListFxRates;
use App\Filament\Resources\FxRates\Schemas\FxRateForm;
use App\Filament\Resources\FxRates\Tables\FxRatesTable;
use App\Models\FxRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FxRateResource extends Resource
{
    protected static ?string $model = FxRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return FxRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FxRatesTable::configure($table);
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
            'index' => ListFxRates::route('/'),
            'create' => CreateFxRate::route('/create'),
            'edit' => EditFxRate::route('/{record}/edit'),
        ];
    }
}
