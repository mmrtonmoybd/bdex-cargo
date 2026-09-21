<?php

declare(strict_types=1);

use App\Enums\NormalizedStatus;
use App\Enums\StatusSource;
use App\Jobs\PollCarrierConsignmentStatus;
use App\Jobs\SyncCarrierConsignmentStatuses;
use App\Models\Carrier;
use App\Models\CarrierConsignment;
use App\Models\CarrierSyncLog;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\Carrier\Adapters\FakeTrackingAdapter;
use App\Services\Carrier\CarrierAdapterFactory;
use App\Services\Carrier\NormalizedTrackingResult;
use App\Services\Tracking\StatusFanOutService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    FakeTrackingAdapter::reset();
});

afterEach(function (): void {
    FakeTrackingAdapter::reset();
});

function carrierResult(NormalizedStatus $status, string $raw = 'Departed facility', ?string $location = 'Dubai'): NormalizedTrackingResult
{
    return new NormalizedTrackingResult(
        status: $status,
        rawStatusText: $raw,
        location: $location,
        occurredAt: Carbon::parse('2026-09-20 08:00:00'),
        rawPayload: ['carrier' => 'FedEx', 'raw' => $raw],
    );
}

describe('issuing tracking numbers', function (): void {
    it('gives an order a BD-EX number that owes nothing to any carrier', function (): void {
        $order = Order::factory()->create();

        expect($order->bdex_tracking_number)->toStartWith('BDX'.now()->format('Ym'))
            ->and($order->carrier_consignment_id)->toBeNull();
    });

    it('creates an order without any carrier existing at all', function (): void {
        expect(Carrier::query()->count())->toBe(0);

        $order = Order::factory()->create();

        expect($order->exists)->toBeTrue()
            ->and($order->status)->toBe(NormalizedStatus::Booked);
    });

    it('never reuses a number', function (): void {
        $numbers = Order::factory()->count(5)->create()->pluck('bdex_tracking_number');

        expect($numbers->unique())->toHaveCount(5);
    });
});

describe('consolidation and fan-out', function (): void {
    it('moves five unrelated orders on one carrier update, identically', function (): void {
        $consignment = CarrierConsignment::factory()->create();
        $orders = Order::factory()->count(5)->create(['carrier_consignment_id' => $consignment->id]);

        FakeTrackingAdapter::willReturn(
            $consignment->carrier_tracking_number,
            carrierResult(NormalizedStatus::InTransit),
        );

        app(PollCarrierConsignmentStatus::class, ['consignment' => $consignment])
            ->handle(app(CarrierAdapterFactory::class), app(StatusFanOutService::class));

        $histories = OrderStatusHistory::query()
            ->whereIn('order_id', $orders->pluck('id'))
            ->get();

        expect($histories)->toHaveCount(5)
            ->and($histories->pluck('status')->unique()->all())->toBe([NormalizedStatus::InTransit])
            ->and($histories->pluck('occurred_at')->map->toIso8601String()->unique())->toHaveCount(1)
            ->and($histories->pluck('source')->unique()->all())->toBe([StatusSource::CarrierSync]);

        foreach ($orders as $order) {
            expect($order->fresh()->status)->toBe(NormalizedStatus::InTransit);
        }
    });

    it('does not touch orders on a different consignment', function (): void {
        $moving = CarrierConsignment::factory()->create();
        $still = CarrierConsignment::factory()->create();

        $movingOrder = Order::factory()->create(['carrier_consignment_id' => $moving->id]);
        $stillOrder = Order::factory()->create(['carrier_consignment_id' => $still->id]);

        FakeTrackingAdapter::willReturn($moving->carrier_tracking_number, carrierResult(NormalizedStatus::Customs));

        app(PollCarrierConsignmentStatus::class, ['consignment' => $moving])
            ->handle(app(CarrierAdapterFactory::class), app(StatusFanOutService::class));

        expect($movingOrder->fresh()->status)->toBe(NormalizedStatus::Customs)
            ->and($stillOrder->fresh()->status)->toBe(NormalizedStatus::Booked);
    });

    it('stays quiet when the carrier reports the same status again', function (): void {
        $consignment = CarrierConsignment::factory()->create(['internal_status' => NormalizedStatus::InTransit]);
        $order = Order::factory()->create(['carrier_consignment_id' => $consignment->id]);

        FakeTrackingAdapter::willReturn($consignment->carrier_tracking_number, carrierResult(NormalizedStatus::InTransit));

        app(PollCarrierConsignmentStatus::class, ['consignment' => $consignment])
            ->handle(app(CarrierAdapterFactory::class), app(StatusFanOutService::class));

        expect(OrderStatusHistory::query()->where('order_id', $order->id)->count())->toBe(0)
            ->and($consignment->fresh()->last_synced_at)->not->toBeNull();
    });
});

describe('sync scheduling and resilience', function (): void {
    it('queues one job per syncable consignment', function (): void {
        Queue::fake();

        CarrierConsignment::factory()->count(3)->create();
        CarrierConsignment::factory()->create(['internal_status' => NormalizedStatus::Delivered]);
        CarrierConsignment::factory()->for(Carrier::factory()->create(['is_active' => false]))->create();

        (new SyncCarrierConsignmentStatuses)->handle();

        Queue::assertPushed(PollCarrierConsignmentStatus::class, 3);
    });

    it('records a failed poll without moving anybody', function (): void {
        $consignment = CarrierConsignment::factory()->create();
        $order = Order::factory()->create(['carrier_consignment_id' => $consignment->id]);

        FakeTrackingAdapter::willFail('Carrier API timed out');

        app(PollCarrierConsignmentStatus::class, ['consignment' => $consignment])
            ->handle(app(CarrierAdapterFactory::class), app(StatusFanOutService::class));

        $log = CarrierSyncLog::query()->sole();

        expect($log->succeeded)->toBeFalse()
            ->and($log->error_message)->toContain('timed out')
            ->and($order->fresh()->status)->toBe(NormalizedStatus::Booked);
    });

    it('logs a successful poll with what changed', function (): void {
        $consignment = CarrierConsignment::factory()->create();
        Order::factory()->count(2)->create(['carrier_consignment_id' => $consignment->id]);

        FakeTrackingAdapter::willReturn($consignment->carrier_tracking_number, carrierResult(NormalizedStatus::Delivered));

        app(PollCarrierConsignmentStatus::class, ['consignment' => $consignment])
            ->handle(app(CarrierAdapterFactory::class), app(StatusFanOutService::class));

        $log = CarrierSyncLog::query()->sole();

        expect($log->succeeded)->toBeTrue()
            ->and($log->status_changed)->toBeTrue()
            ->and($log->orders_affected)->toBe(2)
            ->and($log->normalized_status)->toBe(NormalizedStatus::Delivered);
    });

    it('stops polling a consignment once it is delivered', function (): void {
        Queue::fake();

        CarrierConsignment::factory()->create(['internal_status' => NormalizedStatus::Delivered]);

        (new SyncCarrierConsignmentStatuses)->handle();

        Queue::assertNothingPushed();
    });
});

describe('manual status entries', function (): void {
    it('lets staff move an order that has no carrier yet', function (): void {
        $order = Order::factory()->create();

        $order->recordStatus(NormalizedStatus::PickedUp, StatusSource::Manual, note: 'Collected by our own van');

        expect($order->fresh()->status)->toBe(NormalizedStatus::PickedUp)
            ->and($order->statusHistories()->sole()->source)->toBe(StatusSource::Manual);
    });
});
