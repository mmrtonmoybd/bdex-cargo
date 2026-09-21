<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invoices\Tables;

use App\Models\Invoice;
use App\Services\Documents\InvoiceGenerator;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Searchable invoice history — by number, by shipment, by customer.
 */
class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('issued_at', 'desc')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->fontFamily('mono')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('order.bdex_tracking_number')
                    ->label('Shipment')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('order.sender_name')
                    ->label('Billed to')
                    ->searchable(),
                TextColumn::make('total')
                    ->money(fn (Invoice $record): string => $record->currency)
                    ->sortable(),
                TextColumn::make('order.payment_status')
                    ->label('Payment')
                    ->badge(),
                TextColumn::make('issued_at')
                    ->label('Issued')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('currency')
                    ->options(['AUD' => 'AUD', 'BDT' => 'BDT']),
                Filter::make('unpaid')
                    ->label('Shipment still unpaid')
                    ->query(fn (Builder $query): Builder => $query->whereHas(
                        'order',
                        fn (Builder $order) => $order->whereIn('payment_status', ['pending', 'partially_paid'])
                    )),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Invoice $record): ?string => blank($record->pdf_path)
                        ? null
                        : URL::signedRoute('invoices.download', ['invoice' => $record]))
                    ->openUrlInNewTab()
                    ->visible(fn (Invoice $record): bool => filled($record->pdf_path)
                        && Storage::disk('local')->exists($record->pdf_path)),
                Action::make('regenerate')
                    ->label('Regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalDescription('Re-renders the PDF from the shipment\'s stored breakdown. The invoice number does not change.')
                    ->action(function (Invoice $record): void {
                        app(InvoiceGenerator::class)->generate($record->order);

                        Notification::make()->title('Invoice regenerated')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
