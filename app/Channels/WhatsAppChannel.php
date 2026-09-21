<?php

declare(strict_types=1);

namespace App\Channels;

use App\Services\Notifications\DriverFactory;
use App\Services\Notifications\NotificationDeliveryException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

final class WhatsAppChannel
{
    public function __construct(private readonly DriverFactory $drivers) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $to = $notifiable->routeNotificationFor('whatsapp', $notification)
            ?? $notifiable->phone
            ?? null;

        if (blank($to)) {
            return;
        }

        try {
            $this->drivers->whatsApp()->send((string) $to, (string) $notification->toWhatsApp($notifiable));
        } catch (NotificationDeliveryException $exception) {
            Log::warning('WhatsApp delivery failed', [
                'to' => $to,
                'reason' => $exception->getMessage(),
            ]);
        }
    }
}
