<?php

declare(strict_types=1);

namespace App\Services\Notifications\Drivers;

use App\Services\Config\DynamicConfig;
use App\Services\Notifications\NotificationDeliveryException;
use App\Services\Notifications\SmsDriver;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

/** SSL Wireless — the Bangladesh side, where a local sender ID matters. */
final class SslWirelessSmsDriver implements SmsDriver
{
    public function __construct(private readonly HttpFactory $http) {}

    public function send(string $to, string $message): void
    {
        $token = (string) DynamicConfig::get('sms.ssl_wireless.api_token');
        $sid = (string) DynamicConfig::get('sms.ssl_wireless.sid');
        $url = (string) DynamicConfig::get('sms.ssl_wireless.base_url', 'https://smsplus.sslwireless.com');

        if ($token === '' || $sid === '') {
            throw new NotificationDeliveryException('SSL Wireless is selected as the SMS provider but its credentials are not set under System → Configuration.');
        }

        try {
            $this->http
                ->asJson()
                ->timeout(15)
                ->post(rtrim($url, '/').'/api/v3/send-sms', [
                    'api_token' => $token,
                    'sid' => $sid,
                    'msisdn' => $to,
                    'sms' => $message,
                    'csms_id' => (string) str()->uuid(),
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new NotificationDeliveryException("SSL Wireless rejected the message: {$exception->getMessage()}", previous: $exception);
        }
    }

    public function name(): string
    {
        return 'ssl_wireless';
    }
}
