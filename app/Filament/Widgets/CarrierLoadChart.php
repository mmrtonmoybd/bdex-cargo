<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\Reporting\OperationsMetrics;
use Filament\Widgets\ChartWidget;

/**
 * How work is split across the carriers we actually use.
 *
 * Behind ViewCarrierDetails: this chart names carriers, which is precisely what
 * the rest of the system exists to keep away from customers.
 */
final class CarrierLoadChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Load per carrier';

    protected ?string $description = 'Consignments booked and orders riding on them.';

    protected int|string|array $columnSpan = ['default' => 'full', 'md' => 1];

    protected ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return auth()->user()?->can('ViewCarrierDetails') ?? false;
    }

    protected function getData(): array
    {
        $rows = app(OperationsMetrics::class)->carrierLoad();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $rows->pluck('orders')->all(),
                    'backgroundColor' => '#25e39a',
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Consignments',
                    'data' => $rows->pluck('consignments')->all(),
                    'backgroundColor' => '#c8102e',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $rows->pluck('carrier')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
