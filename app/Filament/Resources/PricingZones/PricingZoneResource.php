<?php

declare(strict_types=1);

namespace App\Filament\Resources\PricingZones;

use App\Filament\Resources\PricingZones\Pages\CreatePricingZone;
use App\Filament\Resources\PricingZones\Pages\EditPricingZone;
use App\Filament\Resources\PricingZones\Pages\ListPricingZones;
use App\Filament\Resources\PricingZones\Schemas\PricingZoneForm;
use App\Filament\Resources\PricingZones\Tables\PricingZonesTable;
use App\Models\PricingZone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PricingZoneResource extends Resource
{
    protected static ?string $model = PricingZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PricingZoneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PricingZonesTable::configure($table);
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
            'index' => ListPricingZones::route('/'),
            'create' => CreatePricingZone::route('/create'),
            'edit' => EditPricingZone::route('/{record}/edit'),
        ];
    }
}
