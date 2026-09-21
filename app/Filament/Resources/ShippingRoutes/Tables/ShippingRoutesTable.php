<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingRoutes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingRoutesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('code')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('corridor')
                    ->label('Corridor')
                    ->state(fn ($record): string => "{$record->origin_country} → {$record->destination_country}"),
                TextColumn::make('currencies')
                    ->label('Currency')
                    ->state(fn ($record): string => "{$record->base_currency} / {$record->quote_currency}"),
                TextColumn::make('transit_time_text')
                    ->label('Transit')
                    ->wrap()
                    ->placeholder('Not published'),
                TextColumn::make('pricing_zones_count')
                    ->label('Zones')
                    ->counts('pricingZones'),
                TextColumn::make('item_categories_count')
                    ->label('Categories')
                    ->counts('itemCategories'),
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
