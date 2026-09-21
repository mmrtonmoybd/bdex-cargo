<?php

declare(strict_types=1);

namespace App\Filament\Resources\FxRates\Schemas;

use App\Enums\FxRateSourceType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FxRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rate')
                    ->description('Quotes use the newest row for a pair. Older rows are kept as history rather than overwritten.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('from_currency')
                                ->label('From')
                                ->required()
                                ->length(3)
                                ->default('AUD'),
                            TextInput::make('to_currency')
                                ->label('To')
                                ->required()
                                ->length(3)
                                ->default('BDT'),
                        ]),
                        TextInput::make('rate')
                            ->helperText('How many units of the target currency one unit of the source buys.')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Grid::make(2)->schema([
                            Select::make('source')
                                ->options(FxRateSourceType::class)
                                ->enum(FxRateSourceType::class)
                                ->default(FxRateSourceType::Manual)
                                ->required(),
                            DateTimePicker::make('fetched_at')
                                ->label('Effective from')
                                ->default(now())
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
