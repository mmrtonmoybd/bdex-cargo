<?php

declare(strict_types=1);

namespace App\Services\Tracking;

use App\Enums\NormalizedStatus;
use App\Models\Document;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Storage;

/**
 * The single gate between an order and anything a customer can see.
 *
 * BD-EX books shipments with real carriers and presents itself as the carrier.
 * That promise is kept here, structurally rather than by care: this class is the
 * only one allowed to build a public tracking payload, it reads an order by a
 * column whitelist, and it never touches — never even eager-loads — the
 * carrierConsignment relation. There is no code path from this class to a
 * carrier's name or its tracking number.
 *
 * A Pest test seeds an order whose carrier data deliberately contains "FedEx" and
 * asserts that neither the HTML page nor the JSON endpoint can be made to render
 * it. If someone later widens this class, that test fails.
 */
final class CustomerFacingOrder
{
    /** Order columns that are safe to read for a customer. Deliberately explicit. */
    private const SAFE_COLUMNS = [
        'id',
        'bdex_tracking_number',
        'shipping_route_id',
        'status',
        'service_level',
        'receiver_name',
        'receiver_city',
        'sender_name',
        'created_at',
        'updated_at',
    ];

    public function find(string $trackingNumber): ?Order
    {
        return Order::query()
            ->select(self::SAFE_COLUMNS)
            ->with([
                'shippingRoute:id,code,name,transit_time_text',
                'statusHistories',
                // Only proof of delivery is ever a customer-visible document.
                'documents' => fn ($query) => $query
                    ->customerVisible()
                    ->where('type', Document::PROOF_OF_DELIVERY),
            ])
            ->where('bdex_tracking_number', strtoupper(trim($trackingNumber)))
            ->first();
    }

    /**
     * The payload the tracking page and the tracking endpoint both render.
     *
     * One method, two surfaces — so there is exactly one definition of what a
     * customer may see, and it cannot drift between HTML and JSON.
     *
     * @return array<string, mixed>
     */
    public function payload(Order $order): array
    {
        $current = $order->status;

        return [
            'tracking_number' => $order->bdex_tracking_number,
            'route' => $order->shippingRoute?->name,
            'status' => $current->value,
            'status_label' => $current->getLabel(),
            'status_detail' => $current->customerDetail(),
            'current_step' => $current->journeyPosition(),
            'estimated_delivery_text' => $order->shippingRoute?->transit_time_text,
            'receiver_name' => $order->receiver_name,
            'timeline' => $this->timeline($order),
            'proof_of_delivery_url' => $this->proofOfDeliveryUrl($order),
        ];
    }

    /**
     * The six published milestones, each marked reached or not.
     *
     * Built from the order's own history — never from the consignment — so an
     * order that has not been consolidated yet still has a truthful timeline.
     *
     * @return array<int, array<string, mixed>>
     */
    private function timeline(Order $order): array
    {
        $reached = $order->statusHistories
            ->keyBy(fn (OrderStatusHistory $history): string => $history->status->value);

        $position = $order->status->journeyPosition();

        return collect(NormalizedStatus::publicJourney())
            ->map(function (NormalizedStatus $step, int $index) use ($reached, $position): array {
                $history = $reached->get($step->value);

                return [
                    'label' => $step->getLabel(),
                    'detail' => $step->customerDetail(),
                    'reached' => $index <= $position,
                    'occurred_at' => $history?->occurred_at?->toIso8601String(),
                    'location' => $history?->location,
                ];
            })
            ->all();
    }

    private function proofOfDeliveryUrl(Order $order): ?string
    {
        $document = $order->documents->first();

        return $document === null ? null : Storage::url($document->path);
    }
}
