<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Reporting\OperationsMetrics;
use Filament\Widgets\ChartWidget;

final class RevenueTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Revenue per month';

    protected ?string $description = 'Paid shipments only, in AUD.';

    protected int|string|array $columnSpan = ['default' => 'full', 'md' => 1];

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $trend = app(OperationsMetrics::class)->monthlyTrend();

        return [
            'datasets' => [[
                'label' => 'Revenue (AUD)',
                'data' => $trend->pluck('revenue')->all(),
                'backgroundColor' => '#12457f',
                'borderRadius' => 6,
            ]],
            'labels' => $trend->pluck('label')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
