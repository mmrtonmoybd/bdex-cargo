<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateOrderFromBooking;
use App\Http\Controllers\Controller;
use App\Services\Compliance\ProhibitedItemScanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The public booking wizard's submit.
 *
 * Creates a real shipment and hands back the BD-EX tracking number, which the
 * customer can use on the tracking widget immediately — before any carrier has
 * been chosen, let alone told about it.
 */
final class BookingController extends Controller
{
    public function __invoke(
        Request $request,
        CreateOrderFromBooking $createOrder,
        ProhibitedItemScanner $scanner,
    ): JsonResponse {
        $data = $request->validate([
            'direction' => ['required', 'in:ausbd,bdaus'],
            'service' => ['nullable', 'string', 'max:60'],
            'date' => ['nullable', 'date', 'after_or_equal:today'],
            'slot' => ['nullable', 'string', 'max:40'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['required', 'string', 'max:40'],
            'sender_address' => ['required', 'string', 'max:2000'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:40'],
            'receiver_address' => ['required', 'string', 'max:2000'],
            'email' => ['nullable', 'email', 'max:255'],
            'boxes' => ['nullable', 'integer', 'min:1', 'max:200'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'category' => ['nullable'],
            'zone' => ['nullable'],
            'item_type' => ['nullable', 'in:garments,non-garments'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'agree' => ['accepted'],
        ], [
            'agree.accepted' => 'Please confirm the packing list and shipping terms before booking.',
            'date.after_or_equal' => 'Choose a pickup date from today onwards.',
        ]);

        $order = $createOrder->handle($data);

        return response()->json([
            'reference' => $order->bdex_tracking_number,
            'quoted_total' => (float) $order->total_cost_aud,
            'currency' => $order->payment_currency,
            // Surfaced so the customer can correct a packing list before a parcel is
            // held at customs, not as a refusal.
            'flagged_items' => $scanner->scanAll([(string) ($data['notes'] ?? '')]),
        ], 201);
    }
}
