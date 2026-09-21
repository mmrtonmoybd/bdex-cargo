<?php

declare(strict_types=1);

namespace App\Filament\Resources\PricingZones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PricingZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shippingRoute.code')
                    ->label('Route')
                    ->badge(),
                TextColumn::make('description')
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('zone_rates_count')
                    ->label('Category rates')
                    ->counts('zoneRates'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('shipping_route_id')
                    ->label('Route')
                    ->relationship('shippingRoute', 'name'),
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
