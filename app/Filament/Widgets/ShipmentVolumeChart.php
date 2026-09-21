<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Reporting\OperationsMetrics;
use Filament\Widgets\ChartWidget;

final class ShipmentVolumeChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Shipments per month';

    protected ?string $description = 'Bookings taken, last twelve months.';

    protected int|string|array $columnSpan = ['default' => 'full', 'md' => 1];

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $trend = app(OperationsMetrics::class)->monthlyTrend();

        return [
            'datasets' => [[
                'label' => 'Shipments',
                'data' => $trend->pluck('orders')->all(),
                'borderColor' => '#25e39a',
                'backgroundColor' => 'rgba(37, 227, 154, 0.18)',
                'fill' => true,
                'tension' => 0.35,
            ]],
            'labels' => $trend->pluck('label')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
