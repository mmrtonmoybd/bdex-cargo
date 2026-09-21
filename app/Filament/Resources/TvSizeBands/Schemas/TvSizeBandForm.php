<?php

declare(strict_types=1);

namespace App\Filament\Resources\TvSizeBands\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TvSizeBandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Screen size')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('shipping_route_id')
                                ->label('Route')
                                ->relationship('shippingRoute', 'name')
                                ->required()
                                ->preload()
                                ->searchable(),
                            TextInput::make('size_label')
                                ->label('Label')
                                ->helperText('As shown to the customer, e.g. 55".')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('min_inches')
                                ->label('From')
                                ->numeric()
                                ->suffix('inches')
                                ->required(),
                            TextInput::make('max_inches')
                                ->label('Up to')
                                ->helperText('Leave empty for "and above".')
                                ->numeric()
                                ->suffix('inches'),
                        ]),
                    ]),

                Section::make('Charges')
                    ->description('Air freight and the airport processing fee are shared across every size and live under System → Configuration.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('volume_fee_aud')
                                ->label('Volume handling')
                                ->numeric()
                                ->prefix('$')
                                ->suffix('AUD')
                                ->minValue(0)
                                ->required(),
                            TextInput::make('default_pallet_weight_kg')
                                ->label('Default pallet weight')
                                ->numeric()
                                ->suffix('kg')
                                ->minValue(0)
                                ->default(12)
                                ->required(),
                            TextInput::make('bd_govt_duty_bdt')
                                ->label('Bangladesh government duty')
                                ->numeric()
                                ->suffix('BDT')
                                ->minValue(0)
                                ->required(),
                            TextInput::make('local_delivery_bdt')
                                ->label('Local home delivery')
                                ->numeric()
                                ->suffix('BDT')
                                ->minValue(0)
                                ->required(),
                        ]),
                    ]),

                Section::make('Visibility')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label('Active')
                                ->helperText('Inactive bands are hidden from the TV calculator.')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }
}
