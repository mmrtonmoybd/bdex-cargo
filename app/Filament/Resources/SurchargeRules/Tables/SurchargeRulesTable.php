<?php

declare(strict_types=1);

namespace App\Filament\Resources\SurchargeRules\Tables;

use App\Enums\CalculationType;
use App\Models\SurchargeRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SurchargeRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->description(fn (SurchargeRule $record): string => $record->code),
                TextColumn::make('shippingRoute.code')
                    ->label('Route')
                    ->badge()
                    ->placeholder('All routes'),
                TextColumn::make('itemCategory.name')
                    ->label('Category')
                    ->placeholder('Whole shipment'),
                TextColumn::make('amount')
                    ->label('Charge')
                    ->state(fn (SurchargeRule $record): string => self::describeAmount($record)),
                TextColumn::make('condition')
                    ->label('Condition')
                    ->state(fn (SurchargeRule $record): string => self::describeCondition($record))
                    ->wrap(),
                IconColumn::make('is_placeholder_amount')
                    ->label('Placeholder')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('warning')
                    ->falseIcon('heroicon-o-check')
                    ->falseColor('gray'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('shipping_route_id')
                    ->label('Route')
                    ->relationship('shippingRoute', 'name'),
                SelectFilter::make('calculation_type')
                    ->options(CalculationType::class),
                Filter::make('is_placeholder_amount')
                    ->label('Needs a confirmed amount')
                    ->query(fn (Builder $query): Builder => $query->where('is_placeholder_amount', true)),
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

    private static function describeAmount(SurchargeRule $rule): string
    {
        $amount = number_format((float) $rule->amount, 2);

        return match ($rule->calculation_type) {
            CalculationType::Flat => "\${$amount}",
            CalculationType::Percentage => "{$amount}% of subtotal",
            CalculationType::PerKg => "\${$amount} per kg",
            CalculationType::PerItem => "\${$amount} per item",
        };
    }

    private static function describeCondition(SurchargeRule $rule): string
    {
        if ($rule->isUnconditional()) {
            return 'Always';
        }

        $field = $rule->condition_field?->getLabel() ?? '';
        $operator = $rule->condition_operator?->getLabel() ?? '';

        return trim("{$field} {$operator} {$rule->condition_value}");
    }
}
