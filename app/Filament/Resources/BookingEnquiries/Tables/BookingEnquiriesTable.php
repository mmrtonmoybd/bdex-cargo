<?php

namespace App\Filament\Resources\BookingEnquiries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingEnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('shippingRoute.name')
                    ->searchable(),
                TextColumn::make('service')
                    ->searchable(),
                TextColumn::make('pickup_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('pickup_slot')
                    ->searchable(),
                TextColumn::make('sender_name')
                    ->searchable(),
                TextColumn::make('sender_phone')
                    ->searchable(),
                TextColumn::make('receiver_name')
                    ->searchable(),
                TextColumn::make('receiver_phone')
                    ->searchable(),
                TextColumn::make('box_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('itemCategory.name')
                    ->searchable(),
                TextColumn::make('quoted_total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quoted_currency')
                    ->searchable(),
                IconColumn::make('terms_accepted')
                    ->boolean(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
