<?php

declare(strict_types=1);

namespace App\Filament\Portal\Widgets;

use App\Enums\PaymentStatus;
use App\Filament\Portal\Resources\Listings\ListingResource;
use App\Models\Order;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * A reseller's own numbers: what they shipped, what it earned, how they are rated.
 */
final class SellerPerformance extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ListingResource::seller() !== null;
    }

    protected function getStats(): array
    {
        $seller = ListingResource::seller();

        if ($seller === null) {
            return [];
        }

        $orders = Order::query()->where('seller_id', $seller->id);

        $revenue = (float) (clone $orders)
            ->where('payment_status', PaymentStatus::Paid->value)
            ->sum('total_cost_aud');

        return [
            Stat::make('Orders placed through you', (string) (clone $orders)->count())
                ->descriptionIcon(Heroicon::ShoppingBag),

            Stat::make('Shipping billed', '$'.number_format($revenue, 2))
                ->description('Before your margin')
                ->descriptionIcon(Heroicon::Banknotes)
                ->color('success'),

            Stat::make('Rating', $seller->rating_count === 0
                ? 'Not rated yet'
                : number_format((float) $seller->rating_average, 1).' / 5')
                ->description($seller->rating_count.' review(s)')
                ->descriptionIcon(Heroicon::Star)
                ->color($seller->rating_average >= 4 ? 'success' : 'warning'),
        ];
    }
}
