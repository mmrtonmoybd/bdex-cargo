<?php

declare(strict_types=1);

namespace App\Services\Notifications\Drivers;

use App\Services\Config\DynamicConfig;
use App\Services\Notifications\NotificationDeliveryException;
use App\Services\Notifications\SmsDriver;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

final class TwilioSmsDriver implements SmsDriver
{
    public function __construct(private readonly HttpFactory $http) {}

    public function send(string $to, string $message): void
    {
        $sid = (string) DynamicConfig::get('sms.twilio.account_sid');
        $token = (string) DynamicConfig::get('sms.twilio.auth_token');
        $from = (string) DynamicConfig::get('sms.twilio.from');

        if ($sid === '' || $token === '') {
            throw new NotificationDeliveryException('Twilio is selected as the SMS provider but its credentials are not set under System → Configuration.');
        }

        try {
            $this->http
                ->withBasicAuth($sid, $token)
                ->asForm()
                ->timeout(15)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To' => $to,
                    'Body' => $message,
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new NotificationDeliveryException("Twilio rejected the message: {$exception->getMessage()}", previous: $exception);
        }
    }

    public function name(): string
    {
        return 'twilio';
    }
}
