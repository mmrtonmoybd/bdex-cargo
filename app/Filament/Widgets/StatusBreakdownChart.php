<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\NormalizedStatus;
use App\Services\Reporting\OperationsMetrics;
use Filament\Widgets\ChartWidget;

/**
 * Where every open shipment currently sits.
 *
 * Labelled with the same vocabulary the customer sees, so the desk and the
 * tracking page describe a parcel the same way.
 */
final class StatusBreakdownChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Shipments by status';

    protected int|string|array $columnSpan = ['default' => 'full', 'md' => 1];

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $counts = app(OperationsMetrics::class)->statusBreakdown();

        $statuses = collect(NormalizedStatus::cases())
            ->filter(fn (NormalizedStatus $status): bool => ($counts[$status->value] ?? 0) > 0);

        return [
            'datasets' => [[
                'label' => 'Shipments',
                'data' => $statuses->map(fn (NormalizedStatus $s): int => (int) $counts[$s->value])->values()->all(),
                'backgroundColor' => $statuses->map(fn (NormalizedStatus $s): string => match ($s) {
                    NormalizedStatus::Delivered => '#00694b',
                    NormalizedStatus::Exception => '#c8102e',
                    NormalizedStatus::Customs => '#f0a202',
                    NormalizedStatus::InTransit => '#12457f',
                    NormalizedStatus::OutForDelivery => '#5b9be0',
                    NormalizedStatus::InWarehouse => '#8fe8c4',
                    NormalizedStatus::PickedUp => '#25e39a',
                    NormalizedStatus::Booked => '#94a3b8',
                })->values()->all(),
            ]],
            'labels' => $statuses->map(fn (NormalizedStatus $s): string => $s->getLabel())->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
