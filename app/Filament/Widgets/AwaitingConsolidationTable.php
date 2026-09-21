<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * The work queue: orders that exist and are being tracked, but have not yet been
 * put on a carrier booking.
 *
 * Deliberately on the dashboard — an order sitting here is a customer who has a
 * tracking number and no movement behind it.
 */
final class AwaitingConsolidationTable extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Waiting to be consolidated';

    public static function canView(): bool
    {
        return auth()->user()?->can('ViewAny:Order') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->awaitingConsolidation()
                    ->with('shippingRoute')
                    ->latest()
            )
            ->emptyStateHeading('Everything is on a consignment')
            ->emptyStateDescription('No booked order is waiting for a carrier booking.')
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('bdex_tracking_number')
                    ->label('BD-EX number')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('shippingRoute.code')
                    ->label('Route')
                    ->badge(),
                TextColumn::make('receiver_city')
                    ->label('To')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Waiting')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record)),
            ]);
    }
}
