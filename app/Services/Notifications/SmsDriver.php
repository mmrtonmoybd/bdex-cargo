<?php

declare(strict_types=1);

namespace App\Services\Notifications;

/**
 * Sending an SMS, without naming a vendor.
 *
 * Which vendor is live is a config_entries value (sms.driver), so switching from
 * one provider to another — or moving back after an outage — is an edit on the
 * Configuration page rather than a deploy. Onboarding a provider nobody has coded
 * against still needs one class implementing this interface; that is the one
 * honest exception, and it is a one-time cost per provider.
 */
interface SmsDriver
{
    /** @throws NotificationDeliveryException */
    public function send(string $to, string $message): void;

    /** Identifier recorded against the delivery, for support and audit. */
    public function name(): string;
}
