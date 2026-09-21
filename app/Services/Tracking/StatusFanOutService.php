<?php

declare(strict_types=1);

namespace App\Services\Tracking;

use App\Enums\StatusSource;
use App\Events\OrderStatusUpdated;
use App\Models\CarrierConsignment;
use App\Services\Carrier\NormalizedTrackingResult;
use Illuminate\Support\Facades\DB;

/**
 * Pushes one carrier movement out to every order riding on it.
 *
 * This is the consolidation rule made concrete: if five customers' parcels went
 * out on one carrier booking, one status change produces five history rows with
 * the same status and the same timestamp, and all five customers see the identical
 * milestone at the identical moment. Per-order rows, rather than one shared row,
 * keep every later query, notification and export working normally.
 *
 * Nothing here writes the carrier's name, its tracking number or its own wording
 * onto an order. Only the normalised status crosses the line.
 */
final class StatusFanOutService
{
    /**
     * @return int the number of orders moved
     */
    public function apply(CarrierConsignment $consignment, NormalizedTrackingResult $result): int
    {
        $changed = $consignment->internal_status !== $result->status;

        return DB::transaction(function () use ($consignment, $result, $changed): int {
            $consignment->forceFill([
                'last_raw_status' => $result->rawStatusText,
                'last_location' => $result->location,
                'raw_payload' => $result->rawPayload,
                'last_synced_at' => now(),
            ]);

            if (! $changed) {
                // Nothing moved. Record that we asked, and leave the customers alone
                // rather than sending an identical notification every twelve hours.
                $consignment->save();

                return 0;
            }

            $consignment->forceFill([
                'internal_status' => $result->status,
                'status_changed_at' => now(),
            ])->save();

            $moved = 0;

            foreach ($consignment->orders()->cursor() as $order) {
                $order->recordStatus(
                    status: $result->status,
                    source: StatusSource::CarrierSync,
                    occurredAt: $result->occurredAt,
                    // The location is a place, not a carrier's identity, so it is safe
                    // to show; the carrier's own status wording is deliberately dropped.
                    location: $result->location,
                );

                OrderStatusUpdated::dispatch($order, $result->status);
                $moved++;
            }

            return $moved;
        });
    }
}
