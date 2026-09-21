<?php

declare(strict_types=1);

namespace App\Filament\Resources\Carriers\Tables;

use App\Models\Carrier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CarriersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->description(fn (Carrier $record): string => $record->code),
                TextColumn::make('driver_class')
                    ->label('Adapter')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge(),
                TextColumn::make('consignments_count')
                    ->label('Consignments')
                    ->counts('consignments'),
                TextColumn::make('poll_interval_hours')
                    ->label('Polled every')
                    ->suffix(' h'),
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
