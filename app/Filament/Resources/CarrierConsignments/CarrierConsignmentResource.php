<?php

declare(strict_types=1);

namespace App\Filament\Resources\CarrierConsignments;

use App\Filament\Resources\CarrierConsignments\Pages\CreateCarrierConsignment;
use App\Filament\Resources\CarrierConsignments\Pages\EditCarrierConsignment;
use App\Filament\Resources\CarrierConsignments\Pages\ListCarrierConsignments;
use App\Filament\Resources\CarrierConsignments\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\CarrierConsignments\Schemas\CarrierConsignmentForm;
use App\Filament\Resources\CarrierConsignments\Tables\CarrierConsignmentsTable;
use App\Models\CarrierConsignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * One real carrier booking, and the orders consolidated onto it.
 *
 * This is where staff do the one thing the customer never sees: attach several
 * unrelated orders to a single carrier tracking number. Gated behind
 * ViewCarrierDetails.
 */
class CarrierConsignmentResource extends Resource
{
    protected static ?string $model = CarrierConsignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static string|UnitEnum|null $navigationGroup = 'Carriers';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'carrier_tracking_number';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewCarrierDetails') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return CarrierConsignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarrierConsignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarrierConsignments::route('/'),
            'create' => CreateCarrierConsignment::route('/create'),
            'edit' => EditCarrierConsignment::route('/{record}/edit'),
        ];
    }
}
