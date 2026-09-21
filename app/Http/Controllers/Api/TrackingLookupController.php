<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingEnquiry;
use Illuminate\Http\JsonResponse;

/**
 * Public tracking lookup.
 *
 * Today this answers for booking references; once orders exist it answers for
 * their BD-EX tracking numbers too. What it will never do is name a carrier or
 * echo a carrier's own tracking number — customers see BD-EX milestones only.
 */
final class TrackingLookupController extends Controller
{
    /** The milestone vocabulary the public site shows, in order. */
    private const STEPS = [
        'new' => ['Booking received', 'Awaiting pickup confirmation'],
        'confirmed' => ['Pickup confirmed', 'Driver assigned'],
        'collected' => ['Picked up', 'With our team'],
        'in_warehouse' => ['In warehouse', 'Weighed and consolidated'],
        'in_transit' => ['In transit', 'On the aircraft'],
        'delivered' => ['Delivered', 'Proof of delivery recorded'],
    ];

    public function __invoke(string $reference): JsonResponse
    {
        $enquiry = BookingEnquiry::query()
            ->where('reference', strtoupper(trim($reference)))
            ->first();

        if ($enquiry === null) {
            return response()->json([
                'found' => false,
                'message' => 'No parcel found for that number. Check the reference on your booking confirmation.',
            ], 404);
        }

        $keys = array_keys(self::STEPS);
        $current = array_search($enquiry->status, $keys, true);
        $current = $current === false ? 0 : $current;

        $steps = [];

        foreach (self::STEPS as $index => [$label, $detail]) {
            $position = array_search($index, $keys, true);
            $steps[] = [
                $label,
                $position <= $current
                    ? strtoupper($enquiry->updated_at->format('D j M · H:i'))
                    : strtoupper($detail),
            ];
        }

        return response()->json([
            'found' => true,
            'reference' => $enquiry->reference,
            'status_label' => strtoupper(str_replace('_', ' ', $enquiry->status)),
            'current_step' => $current,
            'steps' => $steps,
        ]);
    }
}
