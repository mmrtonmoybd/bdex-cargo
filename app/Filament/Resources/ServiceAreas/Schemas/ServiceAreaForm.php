<?php

namespace App\Filament\Resources\ServiceAreas\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('country')
                    ->required(),
                TextInput::make('city')
                    ->required(),
                TextInput::make('region')
                    ->default(null),
                Textarea::make('postcodes')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('supports_pickup')
                    ->required(),
                Toggle::make('supports_delivery')
                    ->required(),
                TextInput::make('standard_days')
                    ->numeric()
                    ->default(null),
                TextInput::make('express_days')
                    ->numeric()
                    ->default(null),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
