<?php

declare(strict_types=1);

namespace App\Filament\Resources\PricingZones\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PricingZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Zone')
                    ->description('Add as many zones per route as the rate card needs. Each new zone needs a rate on every per-kg category before it can be quoted.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('shipping_route_id')
                                ->label('Route')
                                ->relationship('shippingRoute', 'name')
                                ->required()
                                ->preload()
                                ->searchable(),
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->helperText('Optional short code, e.g. NSW.')
                                ->maxLength(255),
                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ]),
                        Textarea::make('description')
                            ->helperText('Which states or districts this covers.')
                            ->rows(2)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
