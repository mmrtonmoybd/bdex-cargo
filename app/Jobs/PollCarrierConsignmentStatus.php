<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\CarrierConsignment;
use App\Models\CarrierSyncLog;
use App\Services\Carrier\CarrierAdapterFactory;
use App\Services\Carrier\CarrierApiException;
use App\Services\Tracking\StatusFanOutService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Asks one carrier where one consignment is, and moves every order riding on it.
 *
 * One job per consignment on purpose: a carrier that is slow or down delays only
 * its own consignments, and never blocks another carrier's orders from updating.
 */
final class PollCarrierConsignmentStatus implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(public readonly CarrierConsignment $consignment) {}

    public function handle(CarrierAdapterFactory $factory, StatusFanOutService $fanOut): void
    {
        $startedAt = microtime(true);
        $consignment = $this->consignment->loadMissing('carrier');

        $log = [
            'carrier_consignment_id' => $consignment->id,
            'carrier_id' => $consignment->carrier_id,
            'attempted_at' => now(),
        ];

        try {
            $result = $factory->make($consignment->carrier)
                ->fetchStatus($consignment->carrier_tracking_number);

            $previous = $consignment->internal_status;
            $moved = $fanOut->apply($consignment, $result);

            CarrierSyncLog::query()->create($log + [
                'succeeded' => true,
                'normalized_status' => $result->status,
                'raw_status' => $result->rawStatusText,
                'status_changed' => $previous !== $result->status,
                'orders_affected' => $moved,
                'duration_ms' => $this->elapsed($startedAt),
            ]);
        } catch (CarrierApiException $exception) {
            // A carrier being unreachable is an operational fact, not a crash. Record
            // it and leave every customer's status exactly as it was.
            CarrierSyncLog::query()->create($log + [
                'succeeded' => false,
                'error_message' => $exception->getMessage(),
                'duration_ms' => $this->elapsed($startedAt),
            ]);
        } catch (Throwable $exception) {
            CarrierSyncLog::query()->create($log + [
                'succeeded' => false,
                'error_message' => 'Unexpected: '.$exception->getMessage(),
                'duration_ms' => $this->elapsed($startedAt),
            ]);

            throw $exception;
        }
    }

    private function elapsed(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
