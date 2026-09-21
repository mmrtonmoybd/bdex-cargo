<?php

declare(strict_types=1);

namespace App\Filament\Resources\FxRates\Tables;

use App\Models\FxRate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FxRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fetched_at', 'desc')
            ->columns([
                TextColumn::make('pair')
                    ->label('Pair')
                    ->state(fn (FxRate $record): string => "{$record->from_currency} → {$record->to_currency}")
                    ->badge(),
                TextColumn::make('rate')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('source')
                    ->badge(),
                TextColumn::make('fetched_at')
                    ->label('Effective from')
                    ->dateTime()
                    ->description(fn (FxRate $record): string => $record->fetched_at->diffForHumans())
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
