<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\AwaitingConsolidationTable;
use App\Filament\Widgets\CarrierLoadChart;
use App\Filament\Widgets\PricingReadiness;
use App\Filament\Widgets\RevenueTrendChart;
use App\Filament\Widgets\ShipmentStats;
use App\Filament\Widgets\ShipmentVolumeChart;
use App\Filament\Widgets\StatusBreakdownChart;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

/**
 * The desk view: what is moving, what is owed, what is stuck.
 *
 * Widgets are listed rather than auto-discovered so their order is deliberate and
 * so the carrier chart can drop out entirely for a role without
 * ViewCarrierDetails, instead of leaving a gap.
 */
final class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    protected static ?int $navigationSort = -2;

    public function getColumns(): int|array
    {
        return ['default' => 1, 'md' => 2];
    }

    public function getWidgets(): array
    {
        return [
            PricingReadiness::class,
            ShipmentStats::class,
            ShipmentVolumeChart::class,
            RevenueTrendChart::class,
            StatusBreakdownChart::class,
            CarrierLoadChart::class,
            AwaitingConsolidationTable::class,
        ];
    }
}
