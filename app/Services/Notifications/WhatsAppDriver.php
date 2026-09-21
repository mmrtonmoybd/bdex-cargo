<?php

declare(strict_types=1);

namespace App\Services\Notifications;

/** Sending a WhatsApp message. Same swap-at-runtime contract as {@see SmsDriver}. */
interface WhatsAppDriver
{
    /** @throws NotificationDeliveryException */
    public function send(string $to, string $message): void;

    public function name(): string;
}
