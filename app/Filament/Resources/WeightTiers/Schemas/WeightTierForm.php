<?php

declare(strict_types=1);

namespace App\Filament\Resources\WeightTiers\Schemas;

use App\Enums\WeightTierScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WeightTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Weight band')
                    ->schema([
                        Select::make('shipping_route_id')
                            ->label('Route')
                            ->relationship('shippingRoute', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Grid::make(3)->schema([
                            TextInput::make('min_kg')
                                ->label('From')
                                ->numeric()
                                ->suffix('kg')
                                ->minValue(0)
                                ->required(),
                            TextInput::make('max_kg')
                                ->label('Up to')
                                ->helperText('Leave empty for "and above".')
                                ->numeric()
                                ->suffix('kg')
                                ->minValue(0),
                            TextInput::make('price_per_kg')
                                ->label('Rate')
                                ->numeric()
                                ->prefix('$')
                                ->suffix('per kg')
                                ->minValue(0)
                                ->required(),
                        ]),
                        Select::make('applies_to')
                            ->label('Applies to')
                            ->options(WeightTierScope::class)
                            ->enum(WeightTierScope::class)
                            ->default(WeightTierScope::GarmentOnly)
                            ->required()
                            ->helperText('Garment-only tiers are skipped when a shipment contains anything else.'),
                        TextInput::make('note')
                            ->label('Footnote')
                            ->helperText('Printed under the published rate table.')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }
}
