<?php

declare(strict_types=1);

namespace App\Filament\Resources\Carriers;

use App\Filament\Resources\Carriers\Pages\CreateCarrier;
use App\Filament\Resources\Carriers\Pages\EditCarrier;
use App\Filament\Resources\Carriers\Pages\ListCarriers;
use App\Filament\Resources\Carriers\Schemas\CarrierForm;
use App\Filament\Resources\Carriers\Tables\CarriersTable;
use App\Models\Carrier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * The real carriers, and who is allowed to know about them.
 *
 * Gated behind the ViewCarrierDetails permission rather than the usual resource
 * CRUD set: knowing which carrier actually flew a shipment is a privilege inside
 * the business, and never leaves it.
 */
class CarrierResource extends Resource
{
    protected static ?string $model = Carrier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Carriers';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewCarrierDetails') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return CarrierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarriersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarriers::route('/'),
            'create' => CreateCarrier::route('/create'),
            'edit' => EditCarrier::route('/{record}/edit'),
        ];
    }
}
