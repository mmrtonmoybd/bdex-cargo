<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\ShippingRoute;
use App\Services\Reporting\ReportBuilder;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

/**
 * Filtered operational reports, on screen and as CSV.
 *
 * The carrier report only appears for a role holding ViewCarrierDetails — the
 * same permission that governs the carrier resources, so there is one answer to
 * "who may know which carrier we used", not two.
 */
final class Reports extends Page
{
    protected string $view = 'filament.pages.reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 9;

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'report' => 'shipments',
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Filters')->schema([
                    Grid::make(4)->schema([
                        Select::make('report')
                            ->label('Report')
                            ->options(fn (): array => app(ReportBuilder::class)->availableReports($this->canSeeCarriers()))
                            ->default('shipments')
                            ->live()
                            ->required(),
                        DatePicker::make('from')->label('From')->live(),
                        DatePicker::make('to')->label('To')->live(),
                        Select::make('route')
                            ->label('Route')
                            ->options(fn (): array => ShippingRoute::query()->pluck('name', 'id')->all())
                            ->placeholder('All routes')
                            ->live()
                            ->visible(fn (Get $get): bool => $get('report') === 'shipments'),
                        Select::make('status')
                            ->label('Status')
                            ->options(fn (): array => app(ReportBuilder::class)->statusOptions())
                            ->placeholder('Any status')
                            ->live()
                            ->visible(fn (Get $get): bool => $get('report') === 'shipments'),
                    ]),
                ]),
            ]);
    }

    /**
     * @return array{title: string, columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<string, string>}
     */
    public function getReport(): array
    {
        // Read the raw filter state rather than validating it: this is a render, and
        // a filter the current role may not use should quietly fall back rather than
        // throw a validation error at the view.
        $state = $this->data;
        $report = $state['report'] ?? 'shipments';

        // A role without the carrier permission cannot reach that report by typing
        // its name into the URL either.
        if ($report === 'carriers' && ! $this->canSeeCarriers()) {
            $report = 'shipments';
        }

        return app(ReportBuilder::class)->build($report, $state);
    }

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon(Heroicon::ArrowDownTray)
                ->action(fn (): StreamedResponse => $this->downloadCsv()),
        ];
    }

    private function downloadCsv(): StreamedResponse
    {
        $report = $this->getReport();
        $filename = str($report['title'])->slug().'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, $report['columns']);

            foreach ($report['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fputcsv($handle, []);

            foreach ($report['totals'] as $label => $value) {
                fputcsv($handle, [$label, $value]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function canSeeCarriers(): bool
    {
        return auth()->user()?->can('ViewCarrierDetails') ?? false;
    }
}
