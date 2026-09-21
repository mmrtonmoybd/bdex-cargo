<?php

declare(strict_types=1);

namespace App\Filament\Portal\Resources\Shipments;

use App\Filament\Portal\Resources\Shipments\Pages\ListShipments;
use App\Filament\Portal\Resources\Shipments\Pages\ViewShipment;
use App\Filament\Portal\Resources\Shipments\Tables\ShipmentsTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * A customer's own shipments.
 *
 * Scoped to the signed-in customer at the query level, and built from a column
 * whitelist that has no path to the carrier relation — the same rule the public
 * tracking page follows, enforced the same way rather than by remembering.
 */
class ShipmentResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'My shipping';

    protected static ?string $modelLabel = 'shipment';

    protected static ?string $recordTitleAttribute = 'bdex_tracking_number';

    /** Columns a customer may see. The carrier's column is not among them. */
    public const VISIBLE_COLUMNS = [
        'id',
        'bdex_tracking_number',
        'air_waybill_number',
        'shipping_route_id',
        'customer_id',
        'status',
        'service_level',
        'sender_name',
        'receiver_name',
        'receiver_city',
        'receiver_address',
        'total_cost_aud',
        'total_cost_bdt',
        'payment_status',
        'paid_amount',
        'pickup_preferred_date',
        'pickup_window',
        'created_at',
        'updated_at',
    ];

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(self::VISIBLE_COLUMNS)
            // Only what the portal renders. Note what is absent: the carrier
            // relation is never loaded here, so nothing downstream can reach it.
            ->with(['shippingRoute:id,code,name,transit_time_text', 'invoice'])
            ->where('customer_id', auth('customer')->id());
    }

    public static function table(Table $table): Table
    {
        return ShipmentsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        // Shipments are booked on the public site, where the quote is shown.
        return false;
    }

    /*
     * Authorisation here is the scope, not a permission.
     *
     * Shield's policies exist for staff, who are granted access to other people's
     * records; a customer is only ever shown their own, enforced by
     * getEloquentQuery(). Deferring to the staff policy would deny every customer,
     * and granting them a staff permission would be far worse than that.
     */
    public static function canViewAny(): bool
    {
        return auth('customer')->check();
    }

    public static function canView(Model $record): bool
    {
        return auth('customer')->check();
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShipments::route('/'),
            'view' => ViewShipment::route('/{record}'),
        ];
    }
}
