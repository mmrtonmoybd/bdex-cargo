<?php

declare(strict_types=1);

use App\Jobs\SyncCarrierConsignmentStatuses;
use Illuminate\Support\Facades\Schedule;

/*
 * Carrier status sync, every twelve hours.
 *
 * One server cron entry drives this:
 *   * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
 *
 * withoutOverlapping keeps a slow carrier from stacking sweeps on top of each
 * other; onOneServer makes it safe if the app is ever run on more than one.
 */
Schedule::job(new SyncCarrierConsignmentStatuses)
    // Midnight and midday. Spelled as cron because the fluent helper is not
    // available on a job schedule in this version.
    ->cron('0 */12 * * *')
    ->name('carrier-status-sync')
    ->withoutOverlapping()
    ->onOneServer();
