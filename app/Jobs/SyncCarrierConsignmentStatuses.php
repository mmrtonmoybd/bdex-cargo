<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\CarrierConsignment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * The twelve-hourly sweep.
 *
 * Selects every consignment still worth asking about — active carrier, not yet
 * delivered — and queues one job each. Scheduled in routes/console.php and driven
 * by the single `schedule:run` cron entry on the server.
 */
final class SyncCarrierConsignmentStatuses implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        CarrierConsignment::query()
            ->syncable()
            ->with('carrier')
            ->cursor()
            ->each(fn (CarrierConsignment $consignment) => PollCarrierConsignmentStatus::dispatch($consignment));
    }
}
