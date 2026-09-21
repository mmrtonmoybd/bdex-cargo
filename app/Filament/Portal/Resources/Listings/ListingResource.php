<?php

declare(strict_types=1);

namespace App\Filament\Portal\Resources\Listings;

use App\Filament\Portal\Resources\Listings\Pages\CreateListing;
use App\Filament\Portal\Resources\Listings\Pages\EditListing;
use App\Filament\Portal\Resources\Listings\Pages\ListListings;
use App\Filament\Portal\Resources\Listings\Schemas\ListingForm;
use App\Filament\Portal\Resources\Listings\Tables\ListingsTable;
use App\Models\Customer;
use App\Models\Seller;
use App\Models\SellerListing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * A seller's own listings — their wording, their margin, our rails.
 *
 * Only visible to a customer who has an approved seller account, so the portal
 * stays simple for everyone else.
 */
class ListingResource extends Resource
{
    protected static ?string $model = SellerListing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Selling';

    protected static ?string $modelLabel = 'listing';

    protected static ?string $recordTitleAttribute = 'title';

    public static function canAccess(): bool
    {
        return self::seller() !== null;
    }

    /* A seller manages their own listings; the scope is the authorisation. */
    public static function canViewAny(): bool
    {
        return self::canAccess();
    }

    public static function canCreate(): bool
    {
        return self::canAccess();
    }

    public static function canView(Model $record): bool
    {
        return self::canAccess();
    }

    public static function canEdit(Model $record): bool
    {
        return self::canAccess();
    }

    public static function canDelete(Model $record): bool
    {
        return self::canAccess();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('seller_id', self::seller()?->id);
    }

    public static function form(Schema $schema): Schema
    {
        return ListingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ListingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListings::route('/'),
            'create' => CreateListing::route('/create'),
            'edit' => EditListing::route('/{record}/edit'),
        ];
    }

    /** The signed-in customer's approved seller account, if they have one. */
    public static function seller(): ?Seller
    {
        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        return $customer === null
            ? null
            : Seller::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'approved')
                ->first();
    }
}
