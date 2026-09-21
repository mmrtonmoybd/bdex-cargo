<?php

declare(strict_types=1);

namespace App\Filament\Resources\PricingAgreements\Tables;

use App\Models\PricingAgreement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PricingAgreementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'desc')
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->description(fn (PricingAgreement $record): string => $record->isContract()
                        ? 'Contract rate'
                        : 'Published volume tier'),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('Anyone who qualifies')
                    ->searchable(),
                TextColumn::make('shippingRoute.code')
                    ->label('Route')
                    ->badge()
                    ->placeholder('All routes'),
                TextColumn::make('threshold')
                    ->label('Qualifies at')
                    ->state(function (PricingAgreement $record): string {
                        $parts = [];

                        if ($record->min_weight_kg !== null) {
                            $parts[] = rtrim(rtrim((string) $record->min_weight_kg, '0'), '.').'kg+';
                        }

                        if ($record->min_shipments_per_month !== null) {
                            $parts[] = $record->min_shipments_per_month.' shipments/month';
                        }

                        return $parts === [] ? 'Always' : implode(' · ', $parts);
                    }),
                TextColumn::make('discount_percent')
                    ->label('Discount')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label('Until')
                    ->date()
                    ->placeholder('Open-ended'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->filters([
                Filter::make('contracts')
                    ->label('Contract rates only')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('customer_id')),
                SelectFilter::make('shipping_route_id')
                    ->label('Route')
                    ->relationship('shippingRoute', 'name'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
