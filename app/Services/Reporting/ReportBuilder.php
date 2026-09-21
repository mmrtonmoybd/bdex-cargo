<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Enums\NormalizedStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Builds the tabular reports the Reports page renders and exports.
 *
 * Each report returns the same shape — columns, rows, totals — so the page, the
 * CSV export and any future PDF all render from one description of the data.
 */
final class ReportBuilder
{
    public function __construct(private readonly OperationsMetrics $metrics) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{title: string, columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<string, string>}
     */
    public function build(string $report, array $filters): array
    {
        $from = isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : now()->startOfMonth();
        $to = isset($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : now()->endOfDay();

        return match ($report) {
            'revenue' => $this->revenue($from, $to),
            'routes' => $this->routes($from, $to),
            'carriers' => $this->carriers(),
            default => $this->shipments($from, $to, $filters),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{title: string, columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<string, string>}
     */
    private function shipments(Carbon $from, Carbon $to, array $filters): array
    {
        $orders = $this->metrics->orders($from, $to)
            ->with(['shippingRoute', 'boxes'])
            ->when(filled($filters['status'] ?? null), fn (Builder $q) => $q->where('status', $filters['status']))
            ->when(filled($filters['route'] ?? null), fn (Builder $q) => $q->where('shipping_route_id', $filters['route']))
            ->latest()
            ->get();

        $rows = $orders->map(fn (Order $order): array => [
            $order->bdex_tracking_number,
            $order->created_at->format('d M Y'),
            $order->shippingRoute?->code ?? '—',
            $order->sender_name,
            $order->receiver_name.($order->receiver_city === null ? '' : ' · '.$order->receiver_city),
            number_format($order->totalWeightKg(), 2).' kg',
            $order->status->getLabel(),
            $order->payment_status->getLabel(),
            '$'.number_format((float) $order->total_cost_aud, 2),
        ])->all();

        return [
            'title' => 'Shipments',
            'columns' => ['BD-EX number', 'Booked', 'Route', 'Sender', 'Receiver', 'Weight', 'Status', 'Payment', 'Total'],
            'rows' => $rows,
            'totals' => [
                'Shipments' => (string) $orders->count(),
                'Total weight' => number_format($orders->sum(fn (Order $o): float => $o->totalWeightKg()), 2).' kg',
                'Value' => '$'.number_format((float) $orders->sum('total_cost_aud'), 2),
            ],
        ];
    }

    /**
     * @return array{title: string, columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<string, string>}
     */
    private function revenue(Carbon $from, Carbon $to): array
    {
        $months = max(1, $from->diffInMonths($to) + 1);
        $trend = $this->metrics->monthlyTrend((int) min(24, $months));

        return [
            'title' => 'Revenue by month',
            'columns' => ['Month', 'Shipments', 'Revenue (paid)', 'Average per shipment'],
            'rows' => $trend->map(fn (object $row): array => [
                $row->label,
                (string) $row->orders,
                '$'.number_format($row->revenue, 2),
                $row->orders === 0 ? '—' : '$'.number_format($row->revenue / $row->orders, 2),
            ])->all(),
            'totals' => [
                'Shipments' => (string) $trend->sum('orders'),
                'Revenue' => '$'.number_format((float) $trend->sum('revenue'), 2),
            ],
        ];
    }

    /**
     * @return array{title: string, columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<string, string>}
     */
    private function routes(Carbon $from, Carbon $to): array
    {
        $rows = $this->metrics->routeBreakdown($from, $to);

        return [
            'title' => 'Corridor performance',
            'columns' => ['Route', 'Shipments', 'Value'],
            'rows' => $rows->map(fn (object $row): array => [
                (string) $row->route,
                (string) $row->orders,
                '$'.number_format((float) $row->revenue, 2),
            ])->all(),
            'totals' => [
                'Shipments' => (string) $rows->sum('orders'),
                'Value' => '$'.number_format((float) $rows->sum('revenue'), 2),
                'Average transit' => ($hours = $this->metrics->averageTransitHours($from, $to)) === null
                    ? '—'
                    : round($hours / 24, 1).' days',
            ],
        ];
    }

    /**
     * Carrier report. The page only offers this to a role holding ViewCarrierDetails.
     *
     * @return array{title: string, columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<string, string>}
     */
    private function carriers(): array
    {
        $rows = $this->metrics->carrierLoad();

        return [
            'title' => 'Carrier load (internal)',
            'columns' => ['Carrier', 'Consignments', 'Orders carried', 'Orders per consignment'],
            'rows' => $rows->map(fn (object $row): array => [
                (string) $row->carrier,
                (string) $row->consignments,
                (string) $row->orders,
                $row->consignments == 0 ? '—' : round($row->orders / $row->consignments, 1).'×',
            ])->all(),
            'totals' => [
                'Consignments' => (string) $rows->sum('consignments'),
                'Orders' => (string) $rows->sum('orders'),
            ],
        ];
    }

    /** @return array<string, string> */
    public function availableReports(bool $includeCarriers): array
    {
        $reports = [
            'shipments' => 'Shipments',
            'revenue' => 'Revenue by month',
            'routes' => 'Corridor performance',
        ];

        if ($includeCarriers) {
            $reports['carriers'] = 'Carrier load (internal)';
        }

        return $reports;
    }

    /** @return array<string, string> */
    public function statusOptions(): array
    {
        return collect(NormalizedStatus::cases())
            ->mapWithKeys(fn (NormalizedStatus $s): array => [$s->value => $s->getLabel()])
            ->all();
    }
}
