<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingRoutes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShippingRouteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Corridor')
                    ->description('Adding a route here makes it available to pricing, booking and quoting. Nothing in the code assumes a fixed set.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('code')
                                ->label('Code')
                                ->helperText('Machine name, e.g. AU_TO_BD. Referenced by seeders and reports.')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('origin_country')
                                ->label('Origin country code')
                                ->helperText('Two-letter ISO code, e.g. AU.')
                                ->required()
                                ->length(2),
                            TextInput::make('destination_country')
                                ->label('Destination country code')
                                ->required()
                                ->length(2),
                        ]),
                    ]),

                Section::make('Currency and transit')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('base_currency')
                                ->label('Charged in')
                                ->helperText('The currency quotes are calculated in.')
                                ->required()
                                ->length(3)
                                ->default('AUD'),
                            TextInput::make('quote_currency')
                                ->label('Also shown as')
                                ->helperText('Converted using the newest stored FX rate for this pair.')
                                ->required()
                                ->length(3)
                                ->default('BDT'),
                        ]),
                        Textarea::make('transit_time_text')
                            ->label('Published transit time')
                            ->helperText('Free text shown to customers. Source materials disagree on this figure — confirm the wording with operations before launch.')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Visibility')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label('Active')
                                ->helperText('Inactive routes disappear from booking and quoting.')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }
}
