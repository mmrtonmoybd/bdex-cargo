<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingEnquiry;
use App\Services\Tracking\CustomerFacingOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

/**
 * Public tracking lookup.
 *
 * Reads through {@see CustomerFacingOrder} and nothing else, so it is
 * structurally incapable of naming a carrier or echoing a carrier's tracking
 * number. A booking reference is also accepted, for the window between someone
 * booking and their order being raised.
 */
final class TrackingLookupController extends Controller
{
    public function __construct(private readonly CustomerFacingOrder $orders) {}

    public function __invoke(string $reference): JsonResponse
    {
        $reference = strtoupper(trim($reference));

        $order = $this->orders->find($reference);

        if ($order !== null) {
            $payload = $this->orders->payload($order);

            return response()->json([
                'found' => true,
                'reference' => $payload['tracking_number'],
                'status_label' => strtoupper($payload['status_label']),
                'current_step' => $payload['current_step'],
                'steps' => $this->steps($payload['timeline']),
                'estimated_delivery_text' => $payload['estimated_delivery_text'],
                'proof_of_delivery_url' => $payload['proof_of_delivery_url'],
            ]);
        }

        $enquiry = BookingEnquiry::query()->where('reference', $reference)->first();

        if ($enquiry !== null) {
            return response()->json([
                'found' => true,
                'reference' => $enquiry->reference,
                'status_label' => 'BOOKING RECEIVED',
                'current_step' => 0,
                'steps' => [
                    ['Booking received', strtoupper($enquiry->created_at->format('D j M · H:i'))],
                    ['Pickup being arranged', 'OUR TEAM WILL CALL WITHIN 24 HOURS'],
                ],
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'No parcel found for that number. Check the reference on your booking confirmation.',
        ], 404);
    }

    /**
     * The design's two-column timeline: what happened, and when.
     *
     * @param  array<int, array<string, mixed>>  $timeline
     * @return array<int, array<int, string>>
     */
    private function steps(array $timeline): array
    {
        return array_map(static function (array $step): array {
            $when = $step['occurred_at'] === null
                ? strtoupper((string) $step['detail'])
                : strtoupper(Carbon::parse($step['occurred_at'])->format('D j M · H:i'));

            $where = $step['location'] === null ? '' : ' · '.strtoupper((string) $step['location']);

            return [(string) $step['label'], $when.$where];
        }, $timeline);
    }
}
