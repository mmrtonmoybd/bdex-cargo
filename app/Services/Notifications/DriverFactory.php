<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Services\Config\DynamicConfig;
use App\Services\Notifications\Drivers\LogSmsDriver;
use App\Services\Notifications\Drivers\LogWhatsAppDriver;
use App\Services\Notifications\Drivers\MetaCloudWhatsAppDriver;
use App\Services\Notifications\Drivers\SslWirelessSmsDriver;
use App\Services\Notifications\Drivers\TwilioSmsDriver;
use Illuminate\Contracts\Container\Container;

/**
 * Resolves whichever provider is currently selected.
 *
 * `sms.driver` and `whatsapp.driver` are config_entries values, so switching
 * provider is one edit on the Configuration page. An unrecognised or unset value
 * falls back to the log driver rather than throwing: a misconfigured provider
 * must not stop an order's status being recorded.
 */
final class DriverFactory
{
    /** @var array<string, class-string<SmsDriver>> */
    private const SMS_DRIVERS = [
        'log' => LogSmsDriver::class,
        'twilio' => TwilioSmsDriver::class,
        'ssl_wireless' => SslWirelessSmsDriver::class,
    ];

    /** @var array<string, class-string<WhatsAppDriver>> */
    private const WHATSAPP_DRIVERS = [
        'log' => LogWhatsAppDriver::class,
        'meta_cloud' => MetaCloudWhatsAppDriver::class,
    ];

    public function __construct(private readonly Container $container) {}

    public function sms(): SmsDriver
    {
        $selected = (string) DynamicConfig::get('sms.driver', 'log');

        return $this->container->make(self::SMS_DRIVERS[$selected] ?? LogSmsDriver::class);
    }

    public function whatsApp(): WhatsAppDriver
    {
        $selected = (string) DynamicConfig::get('whatsapp.driver', 'log');

        return $this->container->make(self::WHATSAPP_DRIVERS[$selected] ?? LogWhatsAppDriver::class);
    }

    /** @return array<string, string> Options for the Configuration page. */
    public static function smsOptions(): array
    {
        return [
            'log' => 'Log only (no provider)',
            'twilio' => 'Twilio',
            'ssl_wireless' => 'SSL Wireless',
        ];
    }

    /** @return array<string, string> */
    public static function whatsAppOptions(): array
    {
        return [
            'log' => 'Log only (no provider)',
            'meta_cloud' => 'WhatsApp Cloud API',
        ];
    }
}
