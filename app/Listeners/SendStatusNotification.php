<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Notifications\OrderStatusChanged;
use App\Services\Config\DynamicConfig;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Notifies the customer whenever their order moves.
 *
 * Queued, so a slow provider never holds up the carrier sync — and skipped
 * entirely for a guest booking with nobody to notify.
 */
final class SendStatusNotification implements ShouldQueue
{
    public function handle(OrderStatusUpdated $event): void
    {
        if (! DynamicConfig::boolean('notifications.status_updates_enabled', true)) {
            return;
        }

        $customer = $event->order->customer;

        if ($customer === null) {
            return;
        }

        $customer->notify(new OrderStatusChanged($event->order, $event->status));
    }
}
