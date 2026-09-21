<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\OrderStatusHistory;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The order's timeline, exactly as the customer sees it.
 *
 * Read-only on purpose: entries are written by the carrier sync or by the
 * "Add status" action, both of which keep the order and its history in step.
 * Editing rows here would let the two disagree.
 */
class StatusHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';

    protected static ?string $title = 'Timeline';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('status')->badge(),
                TextColumn::make('occurred_at')->label('When')->dateTime()->sortable(),
                TextColumn::make('location')->placeholder('—'),
                TextColumn::make('source')
                    ->badge()
                    ->color(fn (OrderStatusHistory $record): string => $record->source->value === 'manual' ? 'warning' : 'gray'),
                TextColumn::make('note')->wrap()->placeholder('—'),
            ]);
    }
}
