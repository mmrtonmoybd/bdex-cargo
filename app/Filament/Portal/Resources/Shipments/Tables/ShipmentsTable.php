<?php

declare(strict_types=1);

namespace App\Filament\Portal\Resources\Shipments\Tables;

use App\Enums\NormalizedStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

class ShipmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No shipments yet')
            ->emptyStateDescription('Book one on the website and it will appear here the moment it is confirmed.')
            ->columns([
                TextColumn::make('bdex_tracking_number')
                    ->label('Tracking number')
                    ->fontFamily('mono')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('shippingRoute.name')
                    ->label('Route'),
                TextColumn::make('receiver_name')
                    ->label('To')
                    ->description(fn (Order $record): ?string => $record->receiver_city)
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('total_cost_aud')
                    ->label('Cost')
                    ->money('AUD'),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Booked')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(NormalizedStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (Order $record): bool => $record->invoice !== null && filled($record->invoice->pdf_path))
                    ->url(fn (Order $record): string => URL::signedRoute('invoices.download', ['invoice' => $record->invoice]))
                    ->openUrlInNewTab(),
            ]);
    }
}
