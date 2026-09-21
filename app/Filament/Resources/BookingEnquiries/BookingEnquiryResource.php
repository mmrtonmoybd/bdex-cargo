<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingEnquiries;

use App\Filament\Resources\BookingEnquiries\Pages\CreateBookingEnquiry;
use App\Filament\Resources\BookingEnquiries\Pages\EditBookingEnquiry;
use App\Filament\Resources\BookingEnquiries\Pages\ListBookingEnquiries;
use App\Filament\Resources\BookingEnquiries\Schemas\BookingEnquiryForm;
use App\Filament\Resources\BookingEnquiries\Tables\BookingEnquiriesTable;
use App\Models\BookingEnquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BookingEnquiryResource extends Resource
{
    protected static ?string $model = BookingEnquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return BookingEnquiryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingEnquiriesTable::configure($table);
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
            'index' => ListBookingEnquiries::route('/'),
            'create' => CreateBookingEnquiry::route('/create'),
            'edit' => EditBookingEnquiry::route('/{record}/edit'),
        ];
    }
}
