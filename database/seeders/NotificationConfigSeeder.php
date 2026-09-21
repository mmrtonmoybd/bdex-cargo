<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ConfigEntry;
use App\Services\Config\DynamicConfig;
use Illuminate\Database\Seeder;

/**
 * Notification provider slots.
 *
 * Both channels start on the log driver, so a fresh system behaves correctly end
 * to end without an account anywhere and without texting a real phone. Switching
 * to a real provider is one edit on the Configuration page.
 */
final class NotificationConfigSeeder extends Seeder
{
    public function run(): void
    {
        $plain = [
            [
                'key' => 'notifications.status_updates_enabled',
                'value' => '1',
                'label' => 'Send status updates to customers',
                'description' => 'Turn off to stop all milestone notifications without changing any provider settings.',
                'sort' => 1,
            ],
            [
                'key' => 'sms.driver',
                'value' => 'log',
                'label' => 'Active SMS provider',
                'description' => 'One of: log, twilio, ssl_wireless. Takes effect on the next message.',
                'sort' => 2,
            ],
            [
                'key' => 'whatsapp.driver',
                'value' => 'log',
                'label' => 'Active WhatsApp provider',
                'description' => 'One of: log, meta_cloud.',
                'sort' => 3,
            ],
            [
                'key' => 'sms.twilio.from',
                'value' => null,
                'label' => 'Twilio · Sender number',
                'description' => 'The number SMS appears to come from.',
                'sort' => 5,
            ],
            [
                'key' => 'sms.ssl_wireless.base_url',
                'value' => 'https://smsplus.sslwireless.com',
                'label' => 'SSL Wireless · Base URL',
                'description' => null,
                'sort' => 8,
            ],
        ];

        foreach ($plain as $entry) {
            ConfigEntry::query()->updateOrCreate(
                ['key' => $entry['key']],
                [
                    'value' => $entry['value'],
                    'is_secret' => false,
                    'group' => 'notifications',
                    'label' => $entry['label'],
                    'description' => $entry['description'],
                    'sort_order' => $entry['sort'],
                ],
            );

            DynamicConfig::forget($entry['key']);
        }

        $secrets = [
            ['sms.twilio.account_sid', 'Twilio · Account SID', 6],
            ['sms.twilio.auth_token', 'Twilio · Auth token', 7],
            ['sms.ssl_wireless.api_token', 'SSL Wireless · API token', 9],
            ['sms.ssl_wireless.sid', 'SSL Wireless · SID', 10],
            ['whatsapp.meta.access_token', 'WhatsApp Cloud · Access token', 11],
            ['whatsapp.meta.phone_number_id', 'WhatsApp Cloud · Phone number ID', 12],
        ];

        foreach ($secrets as [$key, $label, $sort]) {
            DynamicConfig::defineSecret(
                key: $key,
                group: 'notifications',
                label: $label,
                description: 'Stored encrypted. Rotating it here takes effect on the next message.',
                sortOrder: $sort,
            );
        }
    }
}
