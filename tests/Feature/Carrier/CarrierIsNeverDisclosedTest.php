<?php

declare(strict_types=1);

use App\Enums\NormalizedStatus;
use App\Models\Carrier;
use App\Models\CarrierConsignment;
use App\Models\Order;
use App\Services\Carrier\Adapters\FakeTrackingAdapter;
use App\Services\Carrier\CarrierAdapterFactory;
use App\Services\Carrier\NormalizedTrackingResult;
use App\Services\Config\DynamicConfig;
use App\Services\Tracking\CustomerFacingOrder;
use App\Services\Tracking\StatusFanOutService;
use Illuminate\Support\Carbon;

/**
 * BD-EX books with real carriers and presents itself as the carrier.
 *
 * Every fixture below deliberately plants the carrier's name, its tracking number
 * and its own status wording in the database, then asserts that no customer
 * surface can be made to render any of it. This is the test that keeps the
 * promise — not a code review note.
 */
$CARRIER_NAMES = ['Aramex', 'FedEx', 'DHL', 'UPS'];

beforeEach(function (): void {
    FakeTrackingAdapter::reset();

    $this->carrier = Carrier::factory()->create([
        'name' => 'FedEx',
        'code' => 'fedex',
    ]);

    $this->consignment = CarrierConsignment::factory()->for($this->carrier)->create([
        'carrier_tracking_number' => '794658123456',
        'last_raw_status' => 'FedEx Hub — departed Memphis',
    ]);

    // A credential, so the test also covers config leaking through a view.
    DynamicConfig::set('carriers.fedex.access_token', 'fedex-secret-token', isSecret: true);

    $this->order = Order::factory()->create([
        'carrier_consignment_id' => $this->consignment->id,
    ]);

    FakeTrackingAdapter::willReturn('794658123456', new NormalizedTrackingResult(
        status: NormalizedStatus::InTransit,
        rawStatusText: 'FedEx Hub — departed Memphis',
        location: 'Dubai',
        occurredAt: Carbon::parse('2026-09-20 08:00:00'),
        rawPayload: ['carrier' => 'FedEx', 'trackingNumber' => '794658123456'],
    ));

    app(StatusFanOutService::class)->apply(
        $this->consignment,
        app(CarrierAdapterFactory::class)->make($this->carrier)->fetchStatus('794658123456'),
    );
});

afterEach(function (): void {
    FakeTrackingAdapter::reset();
});

it('never leaks a carrier through the public tracking endpoint', function () use ($CARRIER_NAMES): void {
    $body = $this->getJson('/api/track/'.$this->order->bdex_tracking_number)
        ->assertOk()
        ->getContent();

    foreach ($CARRIER_NAMES as $name) {
        expect($body)->not->toContain($name);
    }

    expect($body)->not->toContain('794658123456')
        ->not->toContain('fedex-secret-token')
        ->not->toContain('Memphis')
        ->toContain($this->order->bdex_tracking_number);
});

it('still reports the real progress it learned from that carrier', function (): void {
    $this->getJson('/api/track/'.$this->order->bdex_tracking_number)
        ->assertOk()
        ->assertJsonPath('found', true)
        ->assertJsonPath('status_label', 'IN TRANSIT');
});

it('never leaks a carrier through the customer-facing payload', function () use ($CARRIER_NAMES): void {
    $facing = app(CustomerFacingOrder::class);
    $payload = json_encode($facing->payload($facing->find($this->order->bdex_tracking_number)));

    foreach ([...$CARRIER_NAMES, '794658123456', 'fedex-secret-token', 'Memphis'] as $secret) {
        expect($payload)->not->toContain($secret);
    }
});

it('does not even load the carrier relation on the customer path', function (): void {
    $order = app(CustomerFacingOrder::class)->find($this->order->bdex_tracking_number);

    // Not merely absent from the output — never fetched, so it cannot be reached
    // by a later `toArray()`, a serializer, or a careless view.
    expect($order->relationLoaded('carrierConsignment'))->toBeFalse()
        ->and(array_key_exists('carrier_consignment_id', $order->getAttributes()))->toBeFalse();
});

it('never leaks a carrier through the homepage', function () use ($CARRIER_NAMES): void {
    $html = $this->get('/')->assertOk()->getContent();

    foreach ([...$CARRIER_NAMES, '794658123456', 'fedex-secret-token'] as $secret) {
        expect($html)->not->toContain($secret);
    }
});

it('answers an unknown number without hinting that consolidation exists', function (): void {
    $this->getJson('/api/track/BDX9999999999')
        ->assertNotFound()
        ->assertJsonPath('found', false);
});

it('keeps the carrier readable to staff, where it belongs', function (): void {
    // The abstraction hides the carrier from customers, not from the business.
    expect($this->order->fresh()->carrierConsignment->carrier->name)->toBe('FedEx')
        ->and($this->order->fresh()->carrierConsignment->carrier_tracking_number)->toBe('794658123456');
});
