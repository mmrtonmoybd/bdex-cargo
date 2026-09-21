<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConfigEntries\Tables;

use App\Models\ConfigEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConfigEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultGroup('group')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->description(fn (ConfigEntry $record): string => $record->key),
                TextColumn::make('value')
                    ->label('Value')
                    // A secret is never rendered, not even to someone who may edit it.
                    ->state(fn (ConfigEntry $record): string => $record->is_secret
                        ? '••••••••'
                        : (string) ($record->value ?? '—'))
                    ->limit(60),
                IconColumn::make('is_secret')
                    ->label('Secret')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->falseColor('gray'),
                TextColumn::make('updated_at')
                    ->label('Last changed')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->options(fn (): array => ConfigEntry::query()
                        ->distinct()
                        ->orderBy('group')
                        ->pluck('group', 'group')
                        ->all()),
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
