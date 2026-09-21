<?php

declare(strict_types=1);

namespace App\Filament\Resources\ItemCategories\Schemas;

use App\Enums\PricingModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ItemCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category')
                    ->description('A new category needs no code change — add it here, then set its per-kg rates on the Zone rates tab.')
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
                        ]),
                        Textarea::make('description')
                            ->helperText('Shown on the public rate card and in the booking wizard.')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('How it is priced')
                    ->schema([
                        Select::make('pricing_model')
                            ->label('Pricing model')
                            ->options(PricingModel::class)
                            ->enum(PricingModel::class)
                            ->required()
                            ->live()
                            ->default(PricingModel::PerKgByZone)
                            ->helperText(fn (Get $get): string => match (PricingModel::tryFrom((string) $get('pricing_model'))) {
                                PricingModel::PerKgByZone => 'Rate comes from the Zone rates tab — one rate per zone.',
                                PricingModel::PerKgFlat => 'One rate per kg, the same in every zone. Enter it below.',
                                PricingModel::FlatFee => 'One amount for the whole line, whatever it weighs.',
                                PricingModel::FlatFeePerItem => 'The amount below is charged once per unit of quantity.',
                                default => '',
                            })
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('flat_fee_amount')
                                ->label(fn (Get $get): string => PricingModel::tryFrom((string) $get('pricing_model')) === PricingModel::PerKgFlat
                                    ? 'Rate per kg'
                                    : 'Amount')
                                ->numeric()
                                ->prefix('$')
                                ->minValue(0)
                                ->required(fn (Get $get): bool => PricingModel::tryFrom((string) $get('pricing_model'))?->usesFlatFeeAmount() ?? false)
                                ->visible(fn (Get $get): bool => PricingModel::tryFrom((string) $get('pricing_model'))?->usesFlatFeeAmount() ?? false),

                            TextInput::make('max_weight_kg')
                                ->label('Covered up to (kg)')
                                ->helperText('Display and validation only — e.g. "Laptop (up to 2.5kg)". Leave empty for no ceiling.')
                                ->numeric()
                                ->suffix('kg')
                                ->minValue(0),
                        ]),
                    ]),

                Section::make('Classification')
                    ->schema([
                        Grid::make(3)->schema([
                            Toggle::make('is_garment')
                                ->label('Counts as garments')
                                ->helperText('Drives garment-only weight tiers and the non-garment handling charge.'),
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
