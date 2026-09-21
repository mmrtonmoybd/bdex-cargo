<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FulfilmentMethod;
use App\Enums\NormalizedStatus;
use App\Enums\PaymentStatus;
use App\Enums\ServiceLevel;
use App\Enums\StatusSource;
use App\Models\BookingEnquiry;
use App\Models\Carrier;
use App\Models\CarrierConsignment;
use App\Models\Customer;
use App\Models\FxRate;
use App\Models\Invoice;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\PricingZone;
use App\Models\Seller;
use App\Models\SellerListing;
use App\Models\SellerReview;
use App\Models\ShippingRoute;
use App\Services\Pricing\PricingEngine;
use App\Services\Pricing\QuoteItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * A believable, fully populated system.
 *
 * Everything here is invented — names, addresses, phone numbers — but the shapes
 * are real: quotes come from the pricing engine, tracking numbers from the
 * generator, timelines from the same recordStatus() path the carrier sync uses,
 * and several orders deliberately share one carrier consignment so the
 * consolidation behaviour is visible the moment you open the panel.
 *
 * Run with: php artisan db:seed --class=DemoDataSeeder
 */
final class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $auToBd = ShippingRoute::query()->where('code', 'AU_TO_BD')->firstOrFail();
        $bdToAu = ShippingRoute::query()->where('code', 'BD_TO_AU')->firstOrFail();

        $this->fxRate();
        $customers = $this->customers();
        $sellers = $this->sellers($customers, $auToBd);
        $orders = $this->orders($auToBd, $bdToAu, $customers, $sellers);
        $this->consolidate($orders);
        $this->invoices($orders);
        $this->enquiries($auToBd, $bdToAu);
    }

    private function fxRate(): void
    {
        FxRate::query()->updateOrCreate(
            ['from_currency' => 'AUD', 'to_currency' => 'BDT'],
            ['rate' => 79.4200, 'source' => 'manual', 'fetched_at' => now()->subHours(6)],
        );
    }

    /** @return array<int, Customer> */
    private function customers(): array
    {
        $people = [
            ['Rizwana Haque', 'rizwana.haque@example.com', '0412 884 201', 'Melbourne', ['mail', 'sms']],
            ['Shakib Ahmed', 'shakib.ahmed@example.com', '0431 552 118', 'Sydney', ['mail', 'whatsapp']],
            ['Nasrin Kabir', 'nasrin.kabir@example.com', '0455 770 934', 'Brisbane', ['mail', 'sms', 'whatsapp']],
            ['Tanvir Rahman', 'tanvir.rahman@example.com', '0468 210 776', 'Perth', ['mail']],
            ['Farhana Islam', 'farhana.islam@example.com', '0423 991 502', 'Adelaide', ['mail', 'database']],
            ['Imran Chowdhury', 'imran.chowdhury@example.com', '0490 334 187', 'Sydney', ['mail', 'sms']],
            ['Sadia Noor', 'sadia.noor@example.com', '0402 618 345', 'Canberra', ['mail']],
            ['Mahfuz Alam', 'mahfuz.alam@example.com', '0477 205 660', 'Hobart', ['mail', 'whatsapp']],
        ];

        return collect($people)
            ->map(fn (array $person): Customer => Customer::query()->updateOrCreate(
                ['email' => $person[1]],
                [
                    'name' => $person[0],
                    'phone' => $person[2],
                    'password' => 'password',
                    'preferred_locale' => 'en',
                    'preferred_currency' => 'AUD',
                    'notification_channels' => $person[4],
                    'default_address' => $person[3].', Australia',
                    'email_verified_at' => now()->subMonths(2),
                ],
            ))
            ->all();
    }

    /**
     * @param  array<int, Customer>  $customers
     * @return array<int, Seller>
     */
    private function sellers(array $customers, ShippingRoute $route): array
    {
        $definitions = [
            ['Dhaka Direct Imports', 'dhaka-direct', 8.5, 'approved'],
            ['Bengal Cargo Co.', 'bengal-cargo', 12.0, 'approved'],
        ];

        $sellers = [];

        foreach ($definitions as $index => [$name, $slug, $markup, $status]) {
            $seller = Seller::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'customer_id' => $customers[$index]->id,
                    'business_name' => $name,
                    'contact_email' => $slug.'@example.com',
                    'contact_phone' => '0400 000 '.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'markup_percent' => $markup,
                    'status' => $status,
                ],
            );

            SellerListing::query()->updateOrCreate(
                ['seller_id' => $seller->id, 'title' => 'Household parcels to Bangladesh'],
                [
                    'shipping_route_id' => $route->id,
                    'description' => 'Door-to-door household shipping, packed and cleared for you.',
                    'markup_percent' => $markup,
                    'is_active' => true,
                    'sort_order' => 1,
                ],
            );

            $sellers[] = $seller;
        }

        return $sellers;
    }

    /**
     * @param  array<int, Customer>  $customers
     * @param  array<int, Seller>  $sellers
     * @return array<int, Order>
     */
    private function orders(ShippingRoute $auToBd, ShippingRoute $bdToAu, array $customers, array $sellers): array
    {
        $nsw = PricingZone::query()->where('shipping_route_id', $auToBd->id)->orderBy('sort_order')->firstOrFail();
        $other = PricingZone::query()->where('shipping_route_id', $auToBd->id)->orderBy('sort_order')->skip(1)->first() ?? $nsw;

        $categories = ItemCategory::query()
            ->where('shipping_route_id', $auToBd->id)
            ->with('zoneRates')
            ->get()
            ->keyBy('name');

        // [route, zone, category, weight, boxes, status, city, days ago, paid]
        $plan = [
            [$auToBd, $nsw, 'General goods', 18.0, 2, NormalizedStatus::Delivered, 'Dhaka', 26, true],
            [$auToBd, $nsw, 'Toiletries', 9.5, 1, NormalizedStatus::Delivered, 'Chittagong', 24, true],
            [$auToBd, $other, 'General goods', 22.0, 3, NormalizedStatus::Delivered, 'Sylhet', 22, true],
            [$auToBd, $nsw, 'Electrical appliances', 11.0, 1, NormalizedStatus::Customs, 'Dhaka', 12, true],
            [$auToBd, $nsw, 'Makeup & beauty', 6.0, 1, NormalizedStatus::Customs, 'Dhaka', 12, true],
            [$auToBd, $other, 'Vitamin & supplement', 7.5, 1, NormalizedStatus::Customs, 'Khulna', 11, false],
            [$auToBd, $nsw, 'Tech items & computer equipment', 4.0, 1, NormalizedStatus::InTransit, 'Dhaka', 6, true],
            [$auToBd, $other, 'General goods', 16.0, 2, NormalizedStatus::InTransit, 'Rajshahi', 6, true],
            [$auToBd, $nsw, 'Laptop', 2.5, 1, NormalizedStatus::InTransit, 'Dhaka', 5, true],
            [$auToBd, $nsw, 'Smart Watch', 0.6, 1, NormalizedStatus::InWarehouse, 'Chittagong', 3, false],
            [$auToBd, $other, 'Automobile parts & accessories', 24.0, 2, NormalizedStatus::InWarehouse, 'Dhaka', 3, true],
            [$auToBd, $nsw, 'General goods', 14.0, 1, NormalizedStatus::PickedUp, 'Sylhet', 2, false],
            [$auToBd, $nsw, 'Blood sugar / BP device', 3.2, 1, NormalizedStatus::PickedUp, 'Dhaka', 2, true],
            [$auToBd, $other, 'Toiletries', 8.0, 1, NormalizedStatus::Booked, 'Dhaka', 1, false],
            [$auToBd, $nsw, 'General goods', 30.0, 3, NormalizedStatus::Booked, 'Chittagong', 0, false],
            [$bdToAu, null, null, 12.0, 2, NormalizedStatus::Delivered, 'Sydney', 20, true],
            [$bdToAu, null, null, 7.0, 1, NormalizedStatus::Delivered, 'Melbourne', 18, true],
            [$bdToAu, null, null, 25.0, 3, NormalizedStatus::InTransit, 'Brisbane', 5, true],
            [$bdToAu, null, null, 9.0, 1, NormalizedStatus::InTransit, 'Perth', 4, true],
            [$bdToAu, null, null, 4.5, 1, NormalizedStatus::InWarehouse, 'Sydney', 2, false],
            [$bdToAu, null, null, 16.0, 2, NormalizedStatus::PickedUp, 'Adelaide', 1, false],
            [$bdToAu, null, null, 11.0, 1, NormalizedStatus::Booked, 'Sydney', 0, false],
        ];

        $engine = app(PricingEngine::class);
        $orders = [];

        foreach ($plan as $index => [$route, $zone, $categoryName, $weight, $boxCount, $status, $city, $daysAgo, $paid]) {
            $customer = $customers[$index % count($customers)];
            $bookedAt = now()->subDays($daysAgo)->setTime(9 + ($index % 8), ($index * 7) % 60);

            $quote = $this->quote($engine, $route, $zone, $categories->get($categoryName), $weight, $boxCount);

            $order = Order::query()->create([
                'shipping_route_id' => $route->id,
                'pricing_zone_id' => $zone?->id,
                'customer_id' => $customer->id,
                'seller_id' => $index % 7 === 0 ? $sellers[$index % count($sellers)]->id : null,
                'status' => NormalizedStatus::Booked,
                'service_level' => $index % 5 === 0 ? ServiceLevel::Express : ServiceLevel::Standard,
                'fulfilment_method' => $index % 6 === 0
                    ? FulfilmentMethod::AustraliaPostDropOff
                    : FulfilmentMethod::CourierPickup,
                'sender_name' => $route->code === 'AU_TO_BD' ? $customer->name : 'Sender in '.$city,
                'sender_phone' => $route->code === 'AU_TO_BD' ? $customer->phone : '+8801'.(700000000 + $index),
                'sender_address' => $route->code === 'AU_TO_BD'
                    ? ($customer->default_address ?? 'Sydney, Australia')
                    : 'House '.(12 + $index).', Road 7, Dhanmondi, Dhaka',
                'sender_city' => $route->code === 'AU_TO_BD' ? explode(',', (string) $customer->default_address)[0] : 'Dhaka',
                'receiver_name' => $route->code === 'AU_TO_BD' ? 'Family of '.$customer->name : $customer->name,
                'receiver_phone' => $route->code === 'AU_TO_BD' ? '+8801'.(800000000 + $index) : $customer->phone,
                'receiver_address' => $city,
                'receiver_city' => $city,
                'declared_value' => 120 + ($index * 35),
                'has_product_cover_insurance' => $index % 4 === 0,
                'payment_status' => $paid ? PaymentStatus::Paid : PaymentStatus::Pending,
                'payment_currency' => 'AUD',
                'paid_amount' => $paid ? $quote['total'] : 0,
                'total_cost_aud' => $quote['total'],
                'total_cost_bdt' => $quote['converted'],
                'cost_breakdown' => $quote['breakdown'],
                'is_fragile' => $index % 9 === 0,
                'is_cold_storage' => false,
                'pickup_preferred_date' => $bookedAt->copy()->addDay()->toDateString(),
                'pickup_window' => ['Morning · 9am–1pm', 'Afternoon · 1pm–5pm', 'Evening · 5pm–8pm'][$index % 3],
                'created_at' => $bookedAt,
                'updated_at' => $bookedAt,
            ]);

            $this->boxes($order, $boxCount, $weight, $categoryName);
            $this->walkTimeline($order, $status, $bookedAt);

            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * @return array{total: float, converted: float|null, breakdown: array<string, mixed>|null}
     */
    private function quote(
        PricingEngine $engine,
        ShippingRoute $route,
        ?PricingZone $zone,
        ?ItemCategory $category,
        float $weight,
        int $boxCount,
    ): array {
        try {
            $result = $zone !== null && $category !== null
                ? $engine->quoteAuToBd(collect([new QuoteItem($category, 1, $weight, 250)]), $zone, $boxCount, $weight / max(1, $boxCount))
                : $engine->quoteBdToAu($weight, true, $route, $boxCount);

            return [
                'total' => $result->total(),
                'converted' => $result->convertedTotal(),
                'breakdown' => $result->toArray(),
            ];
        } catch (RuntimeException) {
            // A gap in the rate card should not stop demo data existing; the order
            // simply carries no quote, exactly as it would in production.
            return ['total' => 0.0, 'converted' => null, 'breakdown' => null];
        }
    }

    private function boxes(Order $order, int $boxCount, float $totalWeight, ?string $categoryName): void
    {
        $perBox = round($totalWeight / max(1, $boxCount), 2);

        for ($position = 1; $position <= $boxCount; $position++) {
            $box = $order->boxes()->create([
                'position' => $position,
                'weight_kg' => $perBox,
                'length_cm' => 60,
                'width_cm' => 40,
                'height_cm' => 40,
                'barcode' => 'BX'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT).$position,
            ]);

            $order->items()->create([
                'order_box_id' => $box->id,
                'description' => $categoryName ?? 'Garments — shirts, sarees, kurtis',
                'quantity' => 4,
                'unit' => 'pcs',
                'weight_kg' => $perBox,
                'declared_value' => 80,
            ]);
        }
    }

    /**
     * Walks an order through the milestones it has actually reached.
     *
     * Uses the same recordStatus() path the carrier sync uses, so demo timelines
     * and real ones are built by identical code.
     */
    private function walkTimeline(Order $order, NormalizedStatus $target, Carbon $bookedAt): void
    {
        $journey = NormalizedStatus::publicJourney();
        $stop = $target->journeyPosition();
        $at = $bookedAt->copy();

        foreach ($journey as $index => $status) {
            if ($index > $stop) {
                break;
            }

            $order->recordStatus(
                status: $status,
                source: $index === 0 ? StatusSource::System : StatusSource::CarrierSync,
                occurredAt: $at->copy(),
                location: match ($status) {
                    NormalizedStatus::PickedUp => $order->sender_city,
                    NormalizedStatus::InWarehouse => 'Dhaka hub',
                    NormalizedStatus::InTransit => 'In the air',
                    NormalizedStatus::Customs => 'Destination customs',
                    NormalizedStatus::Delivered => $order->receiver_city,
                    default => null,
                },
            );

            $at->addHours(18 + ($index * 6));
        }
    }

    /** @param array<int, Order> $orders */
    private function consolidate(array $orders): void
    {
        $carriers = Carrier::query()->get()->keyBy('code');

        if ($carriers->isEmpty()) {
            return;
        }

        // Deliberately lopsided: one consignment carries five unrelated customers'
        // orders, which is the case the whole abstraction exists for.
        $groups = [
            ['fedex', '794658123456', NormalizedStatus::Customs, [3, 4, 5, 0, 1]],
            ['dhl', 'JD014600003654321', NormalizedStatus::InTransit, [6, 7, 8]],
            ['aramex', '4587123698', NormalizedStatus::InWarehouse, [9, 10]],
            ['ups', '1Z999AA10123456784', NormalizedStatus::InTransit, [17, 18]],
            ['fedex', '794658987654', NormalizedStatus::Delivered, [15, 16]],
        ];

        foreach ($groups as [$code, $number, $status, $indexes]) {
            $carrier = $carriers->get($code);

            if ($carrier === null) {
                continue;
            }

            $consignment = CarrierConsignment::query()->updateOrCreate(
                ['carrier_id' => $carrier->id, 'carrier_tracking_number' => $number],
                [
                    'internal_status' => $status,
                    'last_raw_status' => $carrier->name.' network scan',
                    'last_location' => 'Dubai',
                    'booked_at' => now()->subDays(8),
                    'last_synced_at' => now()->subHours(4),
                    'status_changed_at' => now()->subHours(4),
                ],
            );

            foreach ($indexes as $index) {
                if (isset($orders[$index])) {
                    $orders[$index]->forceFill(['carrier_consignment_id' => $consignment->id])->save();
                }
            }
        }
    }

    /** @param array<int, Order> $orders */
    private function invoices(array $orders): void
    {
        foreach ($orders as $order) {
            if ($order->payment_status !== PaymentStatus::Paid || $order->total_cost_aud <= 0) {
                continue;
            }

            Invoice::query()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'invoice_number' => Invoice::nextNumber(),
                    'line_items' => $order->cost_breakdown['line_items'] ?? [],
                    'subtotal' => $order->cost_breakdown['subtotal'] ?? $order->total_cost_aud,
                    'tax' => $order->cost_breakdown['tax'] ?? 0,
                    'total' => $order->total_cost_aud,
                    'currency' => 'AUD',
                    'tax_rate_percent' => $order->cost_breakdown['tax_rate_percent'] ?? 0,
                    'issued_at' => $order->created_at,
                ],
            );
        }

        $this->reviews($orders);
    }

    /** @param array<int, Order> $orders */
    private function reviews(array $orders): void
    {
        $delivered = collect($orders)->filter(fn (Order $o): bool => $o->status === NormalizedStatus::Delivered && $o->seller_id !== null);

        $texts = [
            [5, 'Arrived faster than quoted', 'Four boxes from Dhaka to Melbourne, everything intact and eight days door to door.'],
            [5, 'The quote matched the invoice', 'No surprise fees at either end, which is rare in this business.'],
            [4, 'Good service, one slow day at customs', 'Kept informed the whole way, which made the wait fine.'],
        ];

        foreach ($delivered->values() as $index => $order) {
            [$rating, $title, $body] = $texts[$index % count($texts)];

            SellerReview::query()->updateOrCreate(
                ['seller_id' => $order->seller_id, 'order_id' => $order->id],
                [
                    'customer_id' => $order->customer_id,
                    'rating' => $rating,
                    'title' => $title,
                    'body' => $body,
                    'is_published' => true,
                ],
            );
        }
    }

    private function enquiries(ShippingRoute $auToBd, ShippingRoute $bdToAu): void
    {
        $rows = [
            ['Rubel Hossain', '+8801711223344', 'Mirpur 10, Dhaka', 'Ayesha Rahman', '0433 887 221', 'Parramatta NSW 2150', $bdToAu, 3, 14.0, 'new'],
            ['Kamrul Islam', '+8801811556677', 'Agrabad, Chittagong', 'Nusrat Jahan', '0466 102 940', 'Footscray VIC 3011', $bdToAu, 1, 6.5, 'confirmed'],
            ['Priya Das', '0455 220 118', 'Glen Waverley VIC 3150', 'Ratan Das', '+8801911447788', 'Uttara, Dhaka', $auToBd, 2, 19.0, 'collected'],
        ];

        foreach ($rows as $index => [$sender, $senderPhone, $senderAddress, $receiver, $receiverPhone, $receiverAddress, $route, $boxes, $weight, $status]) {
            BookingEnquiry::query()->updateOrCreate(
                ['reference' => 'BDX-'.(2400000 + $index)],
                [
                    'shipping_route_id' => $route->id,
                    'service' => 'standard',
                    'pickup_date' => now()->addDays($index + 1)->toDateString(),
                    'pickup_slot' => 'morning',
                    'sender_name' => $sender,
                    'sender_phone' => $senderPhone,
                    'sender_address' => $senderAddress,
                    'receiver_name' => $receiver,
                    'receiver_phone' => $receiverPhone,
                    'receiver_address' => $receiverAddress,
                    'box_count' => $boxes,
                    'weight_kg' => $weight,
                    'terms_accepted' => true,
                    'status' => $status,
                ],
            );
        }
    }
}
