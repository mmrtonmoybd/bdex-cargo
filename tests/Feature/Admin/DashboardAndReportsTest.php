<?php

declare(strict_types=1);

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Reports;
use App\Filament\Widgets\CarrierLoadChart;
use App\Models\Carrier;
use App\Models\CarrierConsignment;
use App\Models\Order;
use App\Models\User;
use App\Services\Reporting\ReportBuilder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

function staffWith(string ...$permissions): User
{
    $role = Role::query()->create(['name' => 'role-'.Str::random(6), 'guard_name' => 'web']);

    foreach ($permissions as $permission) {
        $role->givePermissionTo(Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
    }

    return User::factory()->create()->assignRole($role);
}

it('renders the dashboard', function (): void {
    Order::factory()->count(3)->create();

    $this->actingAs(staffWith('View:Dashboard', 'ViewAny:Order'));

    livewire(Dashboard::class)->assertSuccessful();
});

it('renders the reports page and its default report', function (): void {
    Order::factory()->count(4)->create();

    $this->actingAs(staffWith('View:Reports'));

    livewire(Reports::class)
        ->assertSuccessful()
        ->assertSee('Shipments');
});

it('offers the carrier report only to a role that may see carriers', function (): void {
    $builder = app(ReportBuilder::class);

    expect(array_keys($builder->availableReports(false)))->not->toContain('carriers')
        ->and(array_keys($builder->availableReports(true)))->toContain('carriers');
});

it('hides the carrier chart from a role without the carrier permission', function (): void {
    $this->actingAs(staffWith('View:Dashboard'));
    expect(CarrierLoadChart::canView())->toBeFalse();

    $this->actingAs(staffWith('View:Dashboard', 'ViewCarrierDetails'));
    expect(CarrierLoadChart::canView())->toBeTrue();
});

it('falls back to the shipments report if a carrier report is requested without permission', function (): void {
    $carrier = Carrier::factory()->create(['name' => 'FedEx']);
    $consignment = CarrierConsignment::factory()->for($carrier)->create();
    Order::factory()->create(['carrier_consignment_id' => $consignment->id]);

    $this->actingAs(staffWith('View:Reports'));

    $report = livewire(Reports::class)
        ->set('data.report', 'carriers')
        ->instance()
        ->getReport();

    expect($report['title'])->toBe('Shipments');
});

it('builds the carrier report for a permitted role', function (): void {
    $carrier = Carrier::factory()->create(['name' => 'FedEx']);
    $consignment = CarrierConsignment::factory()->for($carrier)->create();
    Order::factory()->count(3)->create(['carrier_consignment_id' => $consignment->id]);

    $this->actingAs(staffWith('View:Reports', 'ViewCarrierDetails'));

    $report = livewire(Reports::class)
        ->set('data.report', 'carriers')
        ->instance()
        ->getReport();

    expect($report['title'])->toBe('Carrier load (internal)')
        ->and($report['rows'][0][0])->toBe('FedEx')
        ->and($report['rows'][0][2])->toBe('3');
});
