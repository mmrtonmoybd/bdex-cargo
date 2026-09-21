<?php

declare(strict_types=1);

namespace App\Filament\Resources\CarrierConsignments\Schemas;

use App\Enums\NormalizedStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CarrierConsignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Carrier booking')
                    ->description('Enter the number the carrier gave us once it exists. Several customer orders can share it — that is the point.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('carrier_id')
                                ->label('Carrier')
                                ->relationship('carrier', 'name')
                                ->required()
                                ->preload()
                                ->searchable(),
                            TextInput::make('carrier_tracking_number')
                                ->label('Carrier tracking number')
                                ->helperText('Internal only. Never shown to a customer, on any surface.')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Grid::make(2)->schema([
                            DateTimePicker::make('booked_at')
                                ->label('Booked with carrier')
                                ->default(now()),
                            Select::make('internal_status')
                                ->label('Status')
                                ->options(NormalizedStatus::class)
                                ->enum(NormalizedStatus::class)
                                ->default(NormalizedStatus::Booked)
                                ->helperText('Normally set by the twelve-hourly sync. Change it only to correct a carrier error.')
                                ->required(),
                        ]),
                        Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Last sync')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('last_raw_status')
                                ->label('Carrier wording')
                                ->helperText('What the carrier called it. Kept for staff; never rendered to customers.')
                                ->disabled(),
                            TextInput::make('last_location')
                                ->label('Last location')
                                ->disabled(),
                        ]),
                        DateTimePicker::make('last_synced_at')
                            ->label('Last checked')
                            ->disabled(),
                    ])
                    ->hiddenOn('create'),
            ]);
    }
}
