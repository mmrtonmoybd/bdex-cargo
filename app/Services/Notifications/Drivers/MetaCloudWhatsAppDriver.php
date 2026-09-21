<?php

declare(strict_types=1);

namespace App\Services\Notifications\Drivers;

use App\Services\Config\DynamicConfig;
use App\Services\Notifications\NotificationDeliveryException;
use App\Services\Notifications\WhatsAppDriver;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

/** WhatsApp Cloud API. */
final class MetaCloudWhatsAppDriver implements WhatsAppDriver
{
    public function __construct(private readonly HttpFactory $http) {}

    public function send(string $to, string $message): void
    {
        $token = (string) DynamicConfig::get('whatsapp.meta.access_token');
        $phoneId = (string) DynamicConfig::get('whatsapp.meta.phone_number_id');

        if ($token === '' || $phoneId === '') {
            throw new NotificationDeliveryException('Meta Cloud is selected for WhatsApp but its credentials are not set under System → Configuration.');
        }

        try {
            $this->http
                ->withToken($token)
                ->asJson()
                ->timeout(15)
                ->post("https://graph.facebook.com/v21.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new NotificationDeliveryException("WhatsApp Cloud rejected the message: {$exception->getMessage()}", previous: $exception);
        }
    }

    public function name(): string
    {
        return 'meta_cloud';
    }
}
