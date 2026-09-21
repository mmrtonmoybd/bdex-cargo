<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\FulfilmentMethod;
use App\Enums\NormalizedStatus;
use App\Enums\PaymentStatus;
use App\Enums\ServiceLevel;
use App\Enums\StatusSource;
use App\Events\OrderStatusUpdated;
use App\Models\Customer;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\PricingZone;
use App\Models\ShippingRoute;
use App\Services\Compliance\ProhibitedItemScanner;
use App\Services\Compliance\ServiceAreaChecker;
use App\Services\Pricing\PricingEngine;
use App\Services\Pricing\QuoteItem;
use App\Services\Pricing\QuoteResult;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Turns a completed booking form into a real, trackable shipment.
 *
 * Everything the customer was shown is recalculated here rather than trusted from
 * the browser, and stored on the order — so the quote on the confirmation screen
 * and the quote on the invoice are the same number, produced by the same engine.
 *
 * The order gets its BD-EX tracking number immediately and is fully trackable
 * from this moment. No carrier is involved, or needs to be.
 */
final class CreateOrderFromBooking
{
    public function __construct(
        private readonly PricingEngine $engine,
        private readonly ProhibitedItemScanner $scanner,
        private readonly ServiceAreaChecker $areas,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Order
    {
        $route = ShippingRoute::query()
            ->where('code', ($data['direction'] ?? 'ausbd') === 'ausbd' ? 'AU_TO_BD' : 'BD_TO_AU')
            ->active()
            ->firstOrFail();

        $zone = isset($data['zone']) && is_numeric($data['zone'])
            ? PricingZone::query()->find((int) $data['zone'])
            : null;

        $category = isset($data['category']) && is_numeric($data['category'])
            ? ItemCategory::query()->with('zoneRates')->find((int) $data['category'])
            : null;

        $weight = max(0.5, (float) ($data['weight_kg'] ?? 0));
        $boxCount = max(1, (int) ($data['boxes'] ?? 1));
        $express = ($data['service'] ?? 'standard') === 'express';

        $quote = $this->quote($route, $zone, $category, $weight, $boxCount);

        return DB::transaction(function () use ($data, $route, $zone, $category, $weight, $boxCount, $express, $quote): Order {
            $customer = $this->customer($data);

            $order = Order::query()->create([
                'shipping_route_id' => $route->id,
                'pricing_zone_id' => $zone?->id,
                'customer_id' => $customer?->id,
                'status' => NormalizedStatus::Booked,
                'service_level' => $express ? ServiceLevel::Express : ServiceLevel::Standard,
                'fulfilment_method' => str_contains((string) ($data['service'] ?? ''), 'drop-off')
                    ? FulfilmentMethod::AustraliaPostDropOff
                    : FulfilmentMethod::CourierPickup,
                'sender_name' => $data['sender_name'],
                'sender_phone' => $data['sender_phone'],
                'sender_address' => $data['sender_address'],
                'receiver_name' => $data['receiver_name'],
                'receiver_phone' => $data['receiver_phone'],
                'receiver_address' => $data['receiver_address'],
                'receiver_city' => $this->city($data['receiver_address']),
                'pickup_preferred_date' => $data['date'] ?? null,
                'pickup_window' => $data['slot'] ?? null,
                'payment_status' => PaymentStatus::Pending,
                'payment_currency' => $route->base_currency,
                'total_cost_aud' => $quote?->total() ?? 0,
                'total_cost_bdt' => $quote?->convertedTotal(),
                'cost_breakdown' => $quote?->toArray(),
                'special_instructions' => $this->instructions($data, $route),
            ]);

            $this->boxes($order, $boxCount, $weight, $category, $data['notes'] ?? null);

            $order->recordStatus(
                status: NormalizedStatus::Booked,
                source: StatusSource::System,
                note: 'Booked online.',
            );

            OrderStatusUpdated::dispatch($order, NormalizedStatus::Booked);

            return $order;
        });
    }

    private function quote(
        ShippingRoute $route,
        ?PricingZone $zone,
        ?ItemCategory $category,
        float $weight,
        int $boxCount,
    ): ?QuoteResult {
        try {
            if ($zone !== null && $category !== null) {
                return $this->engine->quoteAuToBd(
                    collect([new QuoteItem($category, 1, $weight)]),
                    $zone,
                    $boxCount,
                    $weight / $boxCount,
                );
            }

            return $this->engine->quoteBdToAu($weight, true, $route, $boxCount);
        } catch (RuntimeException) {
            // The rate card has a gap here. Take the booking anyway and let staff
            // price it by hand — the order records that no quote was issued.
            return null;
        }
    }

    /**
     * Boxes and their packing list.
     *
     * The wizard collects one weight and one set of notes for the shipment, so the
     * weight is split evenly and the notes are attached to the first box. Staff
     * refine both when the parcel is actually weighed.
     */
    private function boxes(Order $order, int $boxCount, float $weight, ?ItemCategory $category, ?string $notes): void
    {
        $perBox = round($weight / $boxCount, 3);
        $description = trim((string) $notes) !== ''
            ? trim((string) $notes)
            : ($category?->name ?? 'Contents to be confirmed at pickup');

        for ($position = 1; $position <= $boxCount; $position++) {
            $box = $order->boxes()->create([
                'position' => $position,
                'weight_kg' => $perBox,
            ]);

            if ($position > 1) {
                continue;
            }

            $flagged = $this->scanner->scan($description);

            $order->items()->create([
                'order_box_id' => $box->id,
                'item_category_id' => $category?->id,
                'description' => $description,
                'quantity' => 1,
                'weight_kg' => $weight,
                'declared_value' => 0,
                'flagged_prohibited' => $flagged !== null,
                'flagged_term' => $flagged,
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    private function customer(array $data): ?Customer
    {
        $email = $data['email'] ?? null;
        $phone = $data['sender_phone'] ?? null;

        if (blank($email) && blank($phone)) {
            return null;
        }

        // Guest bookings are allowed, but a repeat customer should not become a
        // second record just because they did not sign in.
        return Customer::query()->firstOrCreate(
            blank($email) ? ['phone' => $phone] : ['email' => $email],
            [
                'name' => $data['sender_name'] ?? 'Customer',
                'phone' => $phone,
                'notification_channels' => ['mail'],
            ],
        );
    }

    /**
     * Notes staff need at a glance, including a coverage mismatch.
     *
     * @param  array<string, mixed>  $data
     */
    private function instructions(array $data, ShippingRoute $route): ?string
    {
        $notes = [];

        if (filled($data['notes'] ?? null)) {
            $notes[] = trim((string) $data['notes']);
        }

        $city = $this->city($data['receiver_address']);

        if ($this->areas->locate($route->destination_country, $city) === null) {
            $notes[] = 'Delivery address did not match a published service area — confirm coverage before pickup.';
        }

        return $notes === [] ? null : implode("\n\n", $notes);
    }

    /** Best guess at a city from a free-text address, for filtering and coverage. */
    private function city(string $address): ?string
    {
        $parts = array_map('trim', preg_split('/[,\n]/', $address) ?: []);
        $parts = array_values(array_filter($parts));

        return $parts === [] ? null : (string) end($parts);
    }
}
