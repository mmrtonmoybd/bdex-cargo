<?php

namespace App\Filament\Resources\Sellers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SellerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->default(null),
                TextInput::make('business_name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('contact_email')
                    ->email()
                    ->default(null),
                TextInput::make('contact_phone')
                    ->tel()
                    ->default(null),
                TextInput::make('logo_path')
                    ->default(null),
                TextInput::make('markup_percent')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('rating_average')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('rating_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
            ]);
    }
}
