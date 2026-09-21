<?php

declare(strict_types=1);

namespace App\Filament\Portal\Widgets;

use App\Enums\NormalizedStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** What the signed-in customer has in the air, and what they owe. */
final class ShippingSummary extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $customerId = auth('customer')->id();

        $orders = Order::query()->where('customer_id', $customerId);

        $inFlight = (clone $orders)->whereIn('status', [
            NormalizedStatus::PickedUp->value,
            NormalizedStatus::InWarehouse->value,
            NormalizedStatus::InTransit->value,
            NormalizedStatus::Customs->value,
            NormalizedStatus::OutForDelivery->value,
        ])->count();

        $owing = (float) (clone $orders)
            ->whereIn('payment_status', [PaymentStatus::Pending->value, PaymentStatus::PartiallyPaid->value])
            ->sum('total_cost_aud');

        return [
            Stat::make('Shipments', (string) (clone $orders)->count())
                ->description('Everything you have sent with us')
                ->descriptionIcon(Heroicon::ArchiveBox),

            Stat::make('On the move', (string) $inFlight)
                ->description('Picked up, not yet delivered')
                ->descriptionIcon(Heroicon::PaperAirplane)
                ->color($inFlight > 0 ? 'info' : 'gray'),

            Stat::make('Outstanding', '$'.number_format($owing, 2))
                ->description($owing > 0 ? 'Settle in Australia or Bangladesh' : 'Nothing owing')
                ->descriptionIcon(Heroicon::Banknotes)
                ->color($owing > 0 ? 'warning' : 'success'),
        ];
    }
}
