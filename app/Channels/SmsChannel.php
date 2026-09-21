<?php

declare(strict_types=1);

namespace App\Channels;

use App\Services\Notifications\DriverFactory;
use App\Services\Notifications\NotificationDeliveryException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Laravel's notification system, pointed at whichever SMS provider is live.
 */
final class SmsChannel
{
    public function __construct(private readonly DriverFactory $drivers) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $to = $notifiable->routeNotificationFor('sms', $notification)
            ?? $notifiable->phone
            ?? null;

        if (blank($to)) {
            return;
        }

        try {
            $this->drivers->sms()->send((string) $to, (string) $notification->toSms($notifiable));
        } catch (NotificationDeliveryException $exception) {
            // The shipment's status is already recorded and visible on the tracking
            // page; a failed SMS is logged, not escalated into a failed job.
            Log::warning('SMS delivery failed', [
                'to' => $to,
                'reason' => $exception->getMessage(),
            ]);
        }
    }
}
