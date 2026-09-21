<?php

declare(strict_types=1);

namespace App\Filament\Resources\TvSizeBands\Tables;

use App\Models\TvSizeBand;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TvSizeBandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('size_label')
                    ->label('Size')
                    ->searchable()
                    ->description(fn (TvSizeBand $record): string => $record->max_inches === null
                        ? "{$record->min_inches}\" and above"
                        : "{$record->min_inches}–{$record->max_inches}\""),
                TextColumn::make('volume_fee_aud')
                    ->label('Volume fee')
                    ->money('AUD'),
                TextColumn::make('bd_govt_duty_bdt')
                    ->label('BD duty')
                    ->money('BDT'),
                TextColumn::make('local_delivery_bdt')
                    ->label('Local delivery')
                    ->money('BDT'),
                TextColumn::make('default_pallet_weight_kg')
                    ->label('Pallet')
                    ->suffix('kg'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
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
