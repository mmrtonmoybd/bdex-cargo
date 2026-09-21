<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Enums\NormalizedStatus;
use App\Enums\PaymentStatus;
use App\Models\CarrierConsignment;
use App\Models\Order;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * One place that knows how the business measures itself.
 *
 * Widgets and the reports page both read from here rather than each assembling
 * their own query, so a number on the dashboard and the same number in a report
 * cannot disagree.
 */
final class OperationsMetrics
{
    /** @return Builder<Order> */
    public function orders(?Carbon $from = null, ?Carbon $to = null): Builder
    {
        return Order::query()
            ->when($from !== null, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->when($to !== null, fn (Builder $query) => $query->where('created_at', '<=', $to));
    }

    public function orderCount(?Carbon $from = null, ?Carbon $to = null): int
    {
        return $this->orders($from, $to)->count();
    }

    public function revenue(?Carbon $from = null, ?Carbon $to = null): float
    {
        return (float) $this->orders($from, $to)
            ->where('payment_status', PaymentStatus::Paid->value)
            ->sum('total_cost_aud');
    }

    /** Booked work not yet paid for — the number that funds next month. */
    public function outstanding(): float
    {
        return (float) Order::query()
            ->whereIn('payment_status', [PaymentStatus::Pending->value, PaymentStatus::PartiallyPaid->value])
            ->sum('total_cost_aud');
    }

    public function inFlight(): int
    {
        return Order::query()
            ->whereIn('status', [
                NormalizedStatus::PickedUp->value,
                NormalizedStatus::InWarehouse->value,
                NormalizedStatus::InTransit->value,
                NormalizedStatus::Customs->value,
                NormalizedStatus::OutForDelivery->value,
            ])
            ->count();
    }

    /** Orders booked but not yet consolidated onto a carrier booking. */
    public function awaitingConsolidation(): int
    {
        return Order::query()->awaitingConsolidation()->count();
    }

    /** Consignments the sync has not managed to refresh in over a day. */
    public function staleConsignments(): int
    {
        return CarrierConsignment::query()
            ->syncable()
            ->where(function (Builder $query): void {
                $query->whereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<', now()->subDay());
            })
            ->count();
    }

    public function totalWeightKg(?Carbon $from = null, ?Carbon $to = null): float
    {
        return (float) $this->orders($from, $to)
            ->join('order_boxes', 'order_boxes.order_id', '=', 'orders.id')
            ->sum('order_boxes.weight_kg');
    }

    /**
     * Orders and revenue per month, oldest first.
     *
     * @return Collection<int, object{period: string, orders: int, revenue: float}>
     */
    public function monthlyTrend(int $months = 12): Collection
    {
        $from = now()->startOfMonth()->subMonths($months - 1);

        $rows = Order::query()
            ->selectRaw($this->monthExpression().' as period')
            ->selectRaw('count(*) as orders')
            ->selectRaw('sum(case when payment_status = ? then total_cost_aud else 0 end) as revenue', [PaymentStatus::Paid->value])
            ->where('created_at', '>=', $from)
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        // Months with no shipments still belong on the chart; a gap reads as missing
        // data, a zero reads as a quiet month.
        return collect(range(0, $months - 1))
            ->map(function (int $offset) use ($from, $rows): object {
                $period = $from->copy()->addMonths($offset)->format('Y-m');
                $row = $rows->get($period);

                return (object) [
                    'period' => $period,
                    'label' => Carbon::createFromFormat('Y-m', $period)->format('M Y'),
                    'orders' => (int) ($row->orders ?? 0),
                    'revenue' => round((float) ($row->revenue ?? 0), 2),
                ];
            });
    }

    /** @return Collection<string, int> */
    public function statusBreakdown(): Collection
    {
        return Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    /** @return Collection<int, object> */
    public function routeBreakdown(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        return $this->orders($from, $to)
            ->join('shipping_routes', 'shipping_routes.id', '=', 'orders.shipping_route_id')
            ->selectRaw('shipping_routes.name as route, count(*) as orders, sum(orders.total_cost_aud) as revenue')
            ->groupBy('shipping_routes.name')
            ->orderByDesc('orders')
            ->get();
    }

    /**
     * Load per carrier. Internal only — every caller must check ViewCarrierDetails.
     *
     * @return Collection<int, object>
     */
    public function carrierLoad(): Collection
    {
        return CarrierConsignment::query()
            ->join('carriers', 'carriers.id', '=', 'carrier_consignments.carrier_id')
            ->leftJoin('orders', 'orders.carrier_consignment_id', '=', 'carrier_consignments.id')
            ->selectRaw('carriers.name as carrier')
            ->selectRaw('count(distinct carrier_consignments.id) as consignments')
            ->selectRaw('count(orders.id) as orders')
            ->groupBy('carriers.name')
            ->orderByDesc('orders')
            ->get();
    }

    /**
     * Average hours from booking to delivery, for orders that got there.
     */
    public function averageTransitHours(?Carbon $from = null, ?Carbon $to = null): ?float
    {
        $delivered = $this->orders($from, $to)
            ->where('status', NormalizedStatus::Delivered->value)
            ->with('statusHistories')
            ->get();

        if ($delivered->isEmpty()) {
            return null;
        }

        $hours = $delivered
            ->map(function (Order $order): ?float {
                $delivery = $order->statusHistories
                    ->firstWhere('status', NormalizedStatus::Delivered);

                return $delivery === null
                    ? null
                    : $order->created_at->diffInHours($delivery->occurred_at);
            })
            ->filter();

        return $hours->isEmpty() ? null : round((float) $hours->avg(), 1);
    }

    /** Grouping by month, spelled for whichever database is connected. */
    private function monthExpression(): string|Expression
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
    }
}
