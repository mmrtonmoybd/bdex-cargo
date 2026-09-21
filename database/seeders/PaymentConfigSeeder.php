<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ConfigEntry;
use App\Services\Config\DynamicConfig;
use Illuminate\Database\Seeder;

/**
 * Payment gateway slots, one selection per side of the business.
 *
 * Both start on manual confirmation — which is how BD-EX already takes money —
 * so a system with no gateway account still books and settles shipments.
 */
final class PaymentConfigSeeder extends Seeder
{
    public function run(): void
    {
        $plain = [
            ['payment.au.driver', 'manual', 'Australian payment gateway', 'One of: manual, stripe.', 1],
            ['payment.bd.driver', 'manual', 'Bangladesh payment gateway', 'One of: manual, sslcommerz, bkash.', 2],
            ['payment.sslcommerz.base_url', 'https://sandbox.sslcommerz.com', 'SSLCommerz · Base URL', 'Point this at the live host when you go live.', 6],
            ['payment.bkash.base_url', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta', 'bKash · Base URL', 'Point this at the live host when you go live.', 9],
        ];

        foreach ($plain as [$key, $value, $label, $description, $sort]) {
            ConfigEntry::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'is_secret' => false,
                    'group' => 'payments',
                    'label' => $label,
                    'description' => $description,
                    'sort_order' => $sort,
                ],
            );

            DynamicConfig::forget($key);
        }

        $secrets = [
            ['payment.stripe.secret_key', 'Stripe · Secret key', 3],
            ['payment.stripe.webhook_secret', 'Stripe · Webhook signing secret', 4],
            ['payment.sslcommerz.store_id', 'SSLCommerz · Store ID', 5],
            ['payment.sslcommerz.store_password', 'SSLCommerz · Store password', 7],
            ['payment.bkash.app_key', 'bKash · App key', 8],
            ['payment.bkash.app_secret', 'bKash · App secret', 10],
            ['payment.bkash.id_token', 'bKash · ID token', 11],
        ];

        foreach ($secrets as [$key, $label, $sort]) {
            DynamicConfig::defineSecret(
                key: $key,
                group: 'payments',
                label: $label,
                description: 'Stored encrypted. Rotating it here takes effect on the next payment.',
                sortOrder: $sort,
            );
        }
    }
}
