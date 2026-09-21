<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingEnquiry;
use App\Models\ItemCategory;
use App\Models\PricingZone;
use App\Models\ShippingRoute;
use App\Services\Pricing\PricingEngine;
use App\Services\Pricing\QuoteItem;
use App\Services\Pricing\QuoteResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Stores what the booking wizard collected and issues the reference.
 *
 * The quote is recalculated here rather than trusted from the browser, and stored
 * alongside the enquiry, so the figure the customer was shown is on the record.
 */
final class BookingEnquiryController extends Controller
{
    public function __invoke(Request $request, PricingEngine $engine): JsonResponse
    {
        $data = $request->validate([
            'direction' => ['required', 'in:ausbd,bdaus'],
            'service' => ['nullable', 'string', 'max:60'],
            'date' => ['nullable', 'date'],
            'slot' => ['nullable', 'string', 'max:40'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['required', 'string', 'max:40'],
            'sender_address' => ['required', 'string', 'max:2000'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:40'],
            'receiver_address' => ['required', 'string', 'max:2000'],
            'boxes' => ['nullable', 'integer', 'min:1', 'max:200'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'category' => ['nullable'],
            'zone' => ['nullable'],
            'item_type' => ['nullable', 'in:garments,non-garments'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'agree' => ['accepted'],
        ], [
            'agree.accepted' => 'Please confirm the packing list and shipping terms before booking.',
        ]);

        $route = ShippingRoute::query()
            ->where('code', $data['direction'] === 'ausbd' ? 'AU_TO_BD' : 'BD_TO_AU')
            ->first();

        $quote = $this->quote($engine, $data, $route);

        $enquiry = BookingEnquiry::query()->create([
            'reference' => BookingEnquiry::nextReference(),
            'shipping_route_id' => $route?->id,
            'service' => $data['service'] ?? 'standard',
            'pickup_date' => $data['date'] ?? null,
            'pickup_slot' => $data['slot'] ?? null,
            'sender_name' => $data['sender_name'],
            'sender_phone' => $data['sender_phone'],
            'sender_address' => $data['sender_address'],
            'receiver_name' => $data['receiver_name'],
            'receiver_phone' => $data['receiver_phone'],
            'receiver_address' => $data['receiver_address'],
            'box_count' => $data['boxes'] ?? 1,
            'weight_kg' => $data['weight_kg'] ?? 0,
            'item_category_id' => $this->categoryId($data),
            'packing_notes' => $data['notes'] ?? null,
            'quote_snapshot' => $quote?->toArray(),
            'quoted_total' => $quote?->total(),
            'quoted_currency' => $quote?->baseCurrency,
            'terms_accepted' => true,
            'status' => 'new',
        ]);

        return response()->json([
            'reference' => $enquiry->reference,
            'quoted_total' => $enquiry->quoted_total,
        ], 201);
    }

    /** @param array<string, mixed> $data */
    private function quote(PricingEngine $engine, array $data, ?ShippingRoute $route): ?QuoteResult
    {
        if ($route === null) {
            return null;
        }

        $weight = max(0.5, (float) ($data['weight_kg'] ?? 0));

        try {
            if ($data['direction'] === 'ausbd') {
                $zone = PricingZone::query()->find((int) ($data['zone'] ?? 0));
                $category = ItemCategory::query()->with('zoneRates')->find($this->categoryId($data));

                if ($zone === null || $category === null) {
                    return null;
                }

                return $engine->quoteAuToBd(
                    collect([new QuoteItem($category, 1, $weight)]),
                    $zone,
                    (int) ($data['boxes'] ?? 1),
                );
            }

            return $engine->quoteBdToAu(
                $weight,
                ($data['item_type'] ?? 'garments') === 'garments',
                $route,
                (int) ($data['boxes'] ?? 1),
            );
        } catch (RuntimeException) {
            // A gap in the rate card must not stop a customer booking; staff will
            // price it by hand, and the enquiry records that no quote was issued.
            return null;
        }
    }

    /** @param array<string, mixed> $data */
    private function categoryId(array $data): ?int
    {
        return isset($data['category']) && is_numeric($data['category'])
            ? (int) $data['category']
            : null;
    }
}
