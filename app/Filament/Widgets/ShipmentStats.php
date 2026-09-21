<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Reporting\OperationsMetrics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * The numbers someone running the desk needs before anything else.
 */
final class ShipmentStats extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public function getColumns(): int|array
    {
        return ['default' => 1, 'sm' => 2, 'xl' => 4];
    }

    protected function getStats(): array
    {
        $metrics = app(OperationsMetrics::class);

        $monthStart = now()->startOfMonth();
        $thisMonth = $metrics->orderCount($monthStart);
        $lastMonth = $metrics->orderCount(
            $monthStart->copy()->subMonth(),
            $monthStart->copy()->subSecond(),
        );

        $trend = $lastMonth === 0 ? null : round((($thisMonth - $lastMonth) / $lastMonth) * 100);
        $awaiting = $metrics->awaitingConsolidation();
        $stale = $metrics->staleConsignments();

        return [
            Stat::make('Shipments this month', (string) $thisMonth)
                ->description($trend === null
                    ? 'No comparison for last month'
                    : ($trend >= 0 ? "{$trend}% above last month" : abs($trend).'% below last month'))
                ->descriptionIcon($trend !== null && $trend >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->color($trend === null ? 'gray' : ($trend >= 0 ? 'success' : 'warning'))
                ->chart($this->sparkline()),

            Stat::make('Revenue this month', '$'.number_format($metrics->revenue($monthStart), 2))
                ->description('$'.number_format($metrics->outstanding(), 2).' still outstanding')
                ->descriptionIcon(Heroicon::Banknotes)
                ->color('success'),

            Stat::make('In flight now', (string) $metrics->inFlight())
                ->description('Picked up but not yet delivered')
                ->descriptionIcon(Heroicon::PaperAirplane)
                ->color('info'),

            Stat::make('Awaiting consolidation', (string) $awaiting)
                ->description($stale > 0
                    ? "{$stale} consignment(s) not synced in 24h"
                    : 'Carrier sync is up to date')
                ->descriptionIcon($stale > 0 ? Heroicon::ExclamationTriangle : Heroicon::CheckCircle)
                ->color($awaiting > 0 || $stale > 0 ? 'warning' : 'success'),
        ];
    }

    /** @return array<int, int> */
    private function sparkline(): array
    {
        return app(OperationsMetrics::class)
            ->monthlyTrend(6)
            ->pluck('orders')
            ->all();
    }
}
