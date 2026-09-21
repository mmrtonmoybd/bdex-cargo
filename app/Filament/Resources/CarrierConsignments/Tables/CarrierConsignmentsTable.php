<?php

declare(strict_types=1);

namespace App\Filament\Resources\CarrierConsignments\Tables;

use App\Enums\NormalizedStatus;
use App\Jobs\PollCarrierConsignmentStatus;
use App\Models\CarrierConsignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CarrierConsignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('carrier.name')
                    ->label('Carrier')
                    ->badge()
                    ->sortable(),
                TextColumn::make('carrier_tracking_number')
                    ->label('Carrier number')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),
                TextColumn::make('orders_count')
                    ->label('Orders on board')
                    ->counts('orders')
                    ->badge()
                    ->color(fn (int $state): string => $state > 1 ? 'success' : 'gray'),
                TextColumn::make('internal_status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('last_synced_at')
                    ->label('Last checked')
                    ->since()
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('carrier_id')
                    ->label('Carrier')
                    ->relationship('carrier', 'name'),
                SelectFilter::make('internal_status')
                    ->options(NormalizedStatus::class),
            ])
            ->recordActions([
                Action::make('syncNow')
                    ->label('Sync now')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalDescription('Asks the carrier where this consignment is right now. Every order riding on it will move together if the status changed.')
                    ->action(function (CarrierConsignment $record): void {
                        PollCarrierConsignmentStatus::dispatchSync($record);

                        $log = $record->syncLogs()->first();

                        $notification = Notification::make();

                        if ($log?->succeeded) {
                            $notification
                                ->title('Carrier checked')
                                ->body($log->status_changed
                                    ? "Status changed — {$log->orders_affected} order(s) updated."
                                    : 'No change since the last check.')
                                ->success();
                        } else {
                            $notification
                                ->title('Carrier could not be reached')
                                ->body($log?->error_message ?? 'No response recorded.')
                                ->danger();
                        }

                        $notification->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
