<?php

declare(strict_types=1);

namespace App\Filament\Resources\WeightTiers\Tables;

use App\Enums\WeightTierScope;
use App\Models\WeightTier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WeightTiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('shippingRoute.code')
                    ->label('Route')
                    ->badge()
                    ->sortable(),
                TextColumn::make('band')
                    ->label('Weight')
                    ->state(fn (WeightTier $record): string => $record->max_kg === null
                        ? self::trim($record->min_kg).'kg and above'
                        : self::trim($record->min_kg).'–'.self::trim($record->max_kg).'kg'),
                TextColumn::make('price_per_kg')
                    ->label('Rate per kg')
                    ->money('AUD')
                    ->sortable(),
                TextColumn::make('applies_to')
                    ->label('Applies to')
                    ->badge(),
                TextColumn::make('note')
                    ->label('Footnote')
                    ->placeholder('—')
                    ->wrap(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('shipping_route_id')
                    ->label('Route')
                    ->relationship('shippingRoute', 'name'),
                SelectFilter::make('applies_to')
                    ->options(WeightTierScope::class),
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

    private static function trim(string $value): string
    {
        return rtrim(rtrim($value, '0'), '.');
    }
}
