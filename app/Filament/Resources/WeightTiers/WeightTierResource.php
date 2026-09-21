<?php

declare(strict_types=1);

namespace App\Filament\Resources\WeightTiers;

use App\Filament\Resources\WeightTiers\Pages\CreateWeightTier;
use App\Filament\Resources\WeightTiers\Pages\EditWeightTier;
use App\Filament\Resources\WeightTiers\Pages\ListWeightTiers;
use App\Filament\Resources\WeightTiers\Schemas\WeightTierForm;
use App\Filament\Resources\WeightTiers\Tables\WeightTiersTable;
use App\Models\WeightTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WeightTierResource extends Resource
{
    protected static ?string $model = WeightTier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return WeightTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeightTiersTable::configure($table);
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
            'index' => ListWeightTiers::route('/'),
            'create' => CreateWeightTier::route('/create'),
            'edit' => EditWeightTier::route('/{record}/edit'),
        ];
    }
}
