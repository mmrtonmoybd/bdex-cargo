<?php

declare(strict_types=1);

namespace App\Filament\Resources\ItemCategories\Tables;

use App\Enums\PricingModel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ItemCategoriesTable
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
                    ->badge()
                    ->sortable(),
                TextColumn::make('pricing_model')
                    ->label('Priced')
                    ->badge(),
                TextColumn::make('flat_fee_amount')
                    ->label('Amount')
                    ->money('AUD')
                    ->placeholder('Per zone'),
                TextColumn::make('zone_rates_count')
                    ->label('Zone rates')
                    ->counts('zoneRates')
                    ->placeholder('—'),
                IconColumn::make('is_garment')
                    ->label('Garment')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('shipping_route_id')
                    ->label('Route')
                    ->relationship('shippingRoute', 'name'),
                SelectFilter::make('pricing_model')
                    ->options(PricingModel::class),
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
