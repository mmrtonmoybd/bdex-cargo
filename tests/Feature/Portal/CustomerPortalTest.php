<?php

declare(strict_types=1);

use App\Filament\Portal\Pages\NotificationPreferences;
use App\Filament\Portal\Resources\Listings\ListingResource;
use App\Filament\Portal\Resources\Shipments\Pages\ListShipments;
use App\Filament\Portal\Resources\Shipments\Pages\ViewShipment;
use App\Filament\Portal\Resources\Shipments\ShipmentResource;
use App\Models\Carrier;
use App\Models\CarrierConsignment;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Seller;
use App\Models\ShippingRoute;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel('portal');

    $this->customer = Customer::factory()->create();
    $this->actingAs($this->customer, 'customer');
});

it('shows a customer their own shipments and nobody else\'s', function (): void {
    $mine = Order::factory()->count(2)->create(['customer_id' => $this->customer->id]);
    $theirs = Order::factory()->create(['customer_id' => Customer::factory()->create()->id]);

    livewire(ListShipments::class)
        ->assertCanSeeTableRecords($mine)
        ->assertCanNotSeeTableRecords([$theirs]);
});

it('never exposes the carrier in the portal', function (): void {
    $carrier = Carrier::factory()->create(['name' => 'FedEx']);
    $consignment = CarrierConsignment::factory()->for($carrier)->create(['carrier_tracking_number' => '794658123456']);

    $order = Order::factory()->create([
        'customer_id' => $this->customer->id,
        'carrier_consignment_id' => $consignment->id,
    ]);

    $list = livewire(ListShipments::class)->html();
    $view = livewire(ViewShipment::class, ['record' => $order->id])->html();

    foreach (['FedEx', 'Aramex', 'DHL', 'UPS', '794658123456'] as $secret) {
        expect($list)->not->toContain($secret)
            ->and($view)->not->toContain($secret);
    }

    // And the column is not even selected, so nothing downstream can reach it.
    $loaded = ShipmentResource::getEloquentQuery()->find($order->id);

    expect(array_key_exists('carrier_consignment_id', $loaded->getAttributes()))->toBeFalse();
});

it('shows the shipment timeline', function (): void {
    $order = Order::factory()->create(['customer_id' => $this->customer->id]);

    livewire(ViewShipment::class, ['record' => $order->id])
        ->assertSuccessful()
        ->assertSee($order->bdex_tracking_number)
        ->assertSee('Booked');
});

it('will not open a shipment belonging to someone else', function (): void {
    $theirs = Order::factory()->create(['customer_id' => Customer::factory()->create()->id]);

    livewire(ViewShipment::class, ['record' => $theirs->id]);
})->throws(ModelNotFoundException::class);

it('saves notification preferences but keeps email on', function (): void {
    livewire(NotificationPreferences::class)
        ->fillForm([
            'notification_channels' => ['sms', 'whatsapp'],
            'preferred_locale' => 'bn',
            'preferred_currency' => 'BDT',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $this->customer->fresh();

    expect($fresh->notification_channels)->toContain('sms')
        ->and($fresh->notification_channels)->toContain('whatsapp')
        // Unticking email must not leave a shipment with nowhere to report.
        ->and($fresh->notification_channels)->toContain('mail')
        ->and($fresh->preferred_locale)->toBe('bn');
});

it('hides the selling area from a customer who is not a seller', function (): void {
    expect(ListingResource::canAccess())->toBeFalse();
});

it('shows the selling area to an approved seller, scoped to their own listings', function (): void {
    $seller = Seller::query()->create([
        'customer_id' => $this->customer->id,
        'business_name' => 'Dhaka Direct',
        'slug' => 'dhaka-direct-'.uniqid(),
        'markup_percent' => 10,
        'status' => 'approved',
    ]);

    $other = Seller::query()->create([
        'business_name' => 'Someone else',
        'slug' => 'other-'.uniqid(),
        'status' => 'approved',
    ]);

    $route = ShippingRoute::factory()->create();

    $mine = $seller->listings()->create(['shipping_route_id' => $route->id, 'title' => 'My listing', 'is_active' => true]);
    $theirs = $other->listings()->create(['shipping_route_id' => $route->id, 'title' => 'Their listing', 'is_active' => true]);

    expect(ListingResource::canAccess())->toBeTrue();

    $ids = ListingResource::getEloquentQuery()->pluck('id');

    expect($ids)->toContain($mine->id)
        ->and($ids)->not->toContain($theirs->id);
});

it('does not treat a pending seller application as a seller', function (): void {
    Seller::query()->create([
        'customer_id' => $this->customer->id,
        'business_name' => 'Not yet approved',
        'slug' => 'pending-'.uniqid(),
        'status' => 'pending',
    ]);

    expect(ListingResource::canAccess())->toBeFalse();
});
