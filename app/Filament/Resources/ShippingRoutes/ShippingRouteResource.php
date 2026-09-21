<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingRoutes;

use App\Filament\Resources\ShippingRoutes\Pages\CreateShippingRoute;
use App\Filament\Resources\ShippingRoutes\Pages\EditShippingRoute;
use App\Filament\Resources\ShippingRoutes\Pages\ListShippingRoutes;
use App\Filament\Resources\ShippingRoutes\Schemas\ShippingRouteForm;
use App\Filament\Resources\ShippingRoutes\Tables\ShippingRoutesTable;
use App\Models\ShippingRoute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ShippingRouteResource extends Resource
{
    protected static ?string $model = ShippingRoute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAsiaAustralia;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ShippingRouteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingRoutesTable::configure($table);
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
            'index' => ListShippingRoutes::route('/'),
            'create' => CreateShippingRoute::route('/create'),
            'edit' => EditShippingRoute::route('/{record}/edit'),
        ];
    }
}
