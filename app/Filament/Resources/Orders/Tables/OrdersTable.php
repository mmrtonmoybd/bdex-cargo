<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\NormalizedStatus;
use App\Enums\PaymentStatus;
use App\Enums\StatusSource;
use App\Models\CarrierConsignment;
use App\Models\Order;
use App\Services\Documents\CustomsDeclarationGenerator;
use App\Services\Documents\InvoiceGenerator;
use App\Services\Documents\LabelGenerator;
use App\Services\Payments\PaymentProcessor;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('bdex_tracking_number')
                    ->label('BD-EX number')
                    ->fontFamily('mono')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('shippingRoute.code')
                    ->label('Route')
                    ->badge(),
                TextColumn::make('sender_name')
                    ->label('Sender')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('receiver_name')
                    ->label('Receiver')
                    ->description(fn (Order $record): ?string => $record->receiver_city)
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('carrier_consignment_id')
                    ->label('Consolidated')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-airplane')
                    ->falseIcon('heroicon-o-clock')
                    ->falseColor('warning')
                    // Whether an order is on a carrier booking is operational; which
                    // carrier it is stays behind the carrier permission.
                    ->tooltip(fn (Order $record): string => $record->carrier_consignment_id === null
                        ? 'Awaiting consolidation'
                        : 'On a carrier consignment'),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge(),
                TextColumn::make('total_cost_aud')
                    ->label('Total')
                    ->money('AUD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Booked')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(NormalizedStatus::class),
                SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options(PaymentStatus::class),
                SelectFilter::make('shipping_route_id')
                    ->label('Route')
                    ->relationship('shippingRoute', 'name'),
                Filter::make('awaiting_consolidation')
                    ->label('Not yet on a consignment')
                    ->query(fn (Builder $query): Builder => $query->awaitingConsolidation()),
            ])
            ->recordActions([
                Action::make('addStatus')
                    ->label('Add status')
                    ->icon('heroicon-o-flag')
                    ->schema([
                        Select::make('status')
                            ->options(NormalizedStatus::class)
                            ->enum(NormalizedStatus::class)
                            ->required(),
                        DateTimePicker::make('occurred_at')
                            ->label('When')
                            ->default(now())
                            ->required(),
                        Textarea::make('note')
                            ->label('Note for the record')
                            ->rows(2),
                    ])
                    ->modalDescription('Use this for orders not yet on a carrier consignment, or to correct an automated status.')
                    ->action(function (Order $record, array $data): void {
                        $record->recordStatus(
                            status: $data['status'] instanceof NormalizedStatus
                                ? $data['status']
                                : NormalizedStatus::from($data['status']),
                            source: StatusSource::Manual,
                            occurredAt: Carbon::parse($data['occurred_at']),
                            note: $data['note'] ?? null,
                        );

                        Notification::make()->title('Status recorded')->success()->send();
                    }),
                Action::make('recordPayment')
                    ->label('Record payment')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (Order $record): bool => $record->payment_status !== PaymentStatus::Paid)
                    ->schema([
                        TextInput::make('amount')
                            ->label('Amount received')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0.01)
                            ->default(fn (Order $record): float => max(0, (float) $record->total_cost_aud - (float) $record->paid_amount))
                            ->required(),
                        TextInput::make('reference')
                            ->label('Reference')
                            ->helperText('Bank transfer reference, receipt number, or however it arrived.'),
                    ])
                    ->modalDescription('For money that arrived outside a gateway — a bank transfer, or cash at the counter. The order settles from what actually cleared.')
                    ->action(function (Order $record, array $data): void {
                        app(PaymentProcessor::class)->recordManualPayment(
                            order: $record,
                            amount: (float) $data['amount'],
                            userId: auth()->id(),
                            reference: $data['reference'] ?? null,
                        );

                        Notification::make()
                            ->title('Payment recorded')
                            ->body('The order now reads '.$record->fresh()->payment_status->getLabel().'.')
                            ->success()
                            ->send();
                    }),
                Action::make('paperwork')
                    ->label('Paperwork')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Select::make('document')
                            ->label('Generate')
                            ->options([
                                'invoice' => 'Invoice',
                                'label' => 'Shipping labels (one per box)',
                                'customs' => 'Customs declaration',
                            ])
                            ->default('invoice')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $path = match ($data['document']) {
                            'label' => app(LabelGenerator::class)->forOrder($record),
                            'customs' => app(CustomsDeclarationGenerator::class)->generate($record),
                            default => app(InvoiceGenerator::class)->generate($record)->pdf_path,
                        };

                        Notification::make()
                            ->title('Document generated')
                            ->body('Saved against the shipment — open it from the Documents tab.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assignToConsignment')
                        ->label('Assign to consignment')
                        ->icon('heroicon-o-paper-airplane')
                        ->visible(fn (): bool => auth()->user()?->can('ViewCarrierDetails') ?? false)
                        ->schema([
                            Select::make('carrier_consignment_id')
                                ->label('Consignment')
                                ->options(fn (): array => CarrierConsignment::query()
                                    ->with('carrier')
                                    ->latest()
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(fn (CarrierConsignment $c): array => [
                                        $c->id => "{$c->carrier->name} · {$c->carrier_tracking_number}",
                                    ])
                                    ->all())
                                ->searchable()
                                ->required(),
                        ])
                        ->modalDescription('These orders will ride on one carrier booking. From then on they move together — same status, same timestamp — and none of their customers is told why.')
                        ->action(function (Collection $records, array $data): void {
                            $records->each(fn (Order $order) => $order->forceFill([
                                'carrier_consignment_id' => $data['carrier_consignment_id'],
                            ])->save());

                            Notification::make()
                                ->title($records->count().' order(s) consolidated')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('printLabels')
                        ->label('Print labels')
                        ->icon('heroicon-o-printer')
                        ->modalDescription('Produces one PDF containing every box label for the selected shipments — fifty labels in one or two clicks rather than fifty.')
                        ->action(function (Collection $records): void {
                            app(LabelGenerator::class)->forOrders($records);

                            Notification::make()
                                ->title('Labels ready')
                                ->body($records->count().' shipment(s) — open the batch from any of their Documents tabs.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('generateInvoices')
                        ->label('Generate invoices')
                        ->icon('heroicon-o-document-currency-dollar')
                        ->action(function (Collection $records): void {
                            $generator = app(InvoiceGenerator::class);

                            $records->each(fn (Order $order) => $generator->generate($order));

                            Notification::make()
                                ->title($records->count().' invoice(s) issued')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
