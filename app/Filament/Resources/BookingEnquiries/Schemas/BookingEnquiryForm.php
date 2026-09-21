<?php

namespace App\Filament\Resources\BookingEnquiries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BookingEnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->required(),
                Select::make('shipping_route_id')
                    ->relationship('shippingRoute', 'name')
                    ->default(null),
                TextInput::make('service')
                    ->required()
                    ->default('standard'),
                DatePicker::make('pickup_date'),
                TextInput::make('pickup_slot')
                    ->default(null),
                TextInput::make('sender_name')
                    ->default(null),
                TextInput::make('sender_phone')
                    ->tel()
                    ->default(null),
                Textarea::make('sender_address')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('receiver_name')
                    ->default(null),
                TextInput::make('receiver_phone')
                    ->tel()
                    ->default(null),
                Textarea::make('receiver_address')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('box_count')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('weight_kg')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('item_category_id')
                    ->relationship('itemCategory', 'name')
                    ->default(null),
                Textarea::make('packing_notes')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('quote_snapshot')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('quoted_total')
                    ->numeric()
                    ->default(null),
                TextInput::make('quoted_currency')
                    ->default(null),
                Toggle::make('terms_accepted')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('new'),
            ]);
    }
}
