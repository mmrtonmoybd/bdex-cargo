<?php

declare(strict_types=1);

namespace App\Filament\Resources\PricingAgreements\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PricingAgreementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Agreement')
                ->description('Leave the customer empty for a published volume tier anyone can earn. Name a customer to make it their contract rate.')
                ->schema([
                    TextInput::make('label')
                        ->helperText('Appears on the customer\'s itemised quote, so word it the way you want it read.')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')->rows(2)->columnSpanFull(),
                    Grid::make(2)->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->placeholder('Anyone who qualifies')
                            ->searchable()
                            ->preload(),
                        Select::make('shipping_route_id')
                            ->label('Route')
                            ->relationship('shippingRoute', 'name')
                            ->placeholder('Every route')
                            ->searchable()
                            ->preload(),
                    ]),
                ]),

            Section::make('When it applies')
                ->description('Leave both thresholds empty for a rate that always applies — which is what a contract usually is.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('min_weight_kg')
                            ->label('From weight')
                            ->numeric()
                            ->suffix('kg')
                            ->minValue(0),
                        TextInput::make('min_shipments_per_month')
                            ->label('From shipments this month')
                            ->helperText('Counted against the customer\'s own shipments in the current month.')
                            ->numeric()
                            ->minValue(0),
                    ]),
                    Grid::make(2)->schema([
                        DatePicker::make('valid_from')->label('Valid from'),
                        DatePicker::make('valid_until')->label('Valid until')->helperText('Leave empty for open-ended.'),
                    ]),
                ]),

            Section::make('Discount')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('discount_percent')
                            ->label('Discount')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('priority')
                            ->helperText('Higher wins when two agreements both qualify.')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')->label('Active')->default(true),
                    ]),
                ]),
        ]);
    }
}
