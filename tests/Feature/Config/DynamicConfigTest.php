<?php

declare(strict_types=1);

use App\Models\ConfigEntry;
use App\Services\Config\DynamicConfig;
use Illuminate\Support\Facades\Crypt;

it('returns the default when the key does not exist', function (): void {
    expect(DynamicConfig::get('nothing.here', 'fallback'))->toBe('fallback');
});

it('reads a plain value', function (): void {
    DynamicConfig::set('sms.driver', 'twilio');

    expect(DynamicConfig::get('sms.driver'))->toBe('twilio');
});

it('stores a secret encrypted and hands it back in the clear', function (): void {
    DynamicConfig::set('carriers.fedex.client_secret', 'super-secret', isSecret: true);

    $stored = ConfigEntry::query()->where('key', 'carriers.fedex.client_secret')->sole();

    expect($stored->value)->not->toBe('super-secret')
        ->and($stored->is_secret)->toBeTrue()
        ->and(Crypt::decryptString($stored->value))->toBe('super-secret')
        ->and(DynamicConfig::get('carriers.fedex.client_secret'))->toBe('super-secret');
});

it('reflects an edit on the next read without a deploy', function (): void {
    DynamicConfig::set('payment.au.driver', 'stripe');
    expect(DynamicConfig::get('payment.au.driver'))->toBe('stripe');

    DynamicConfig::set('payment.au.driver', 'other');
    expect(DynamicConfig::get('payment.au.driver'))->toBe('other');
});

it('casts typed reads', function (): void {
    DynamicConfig::set('pricing.tax_rate_percent', '7.5');
    DynamicConfig::set('feature.enabled', '1');
    DynamicConfig::set('retry.count', '3');

    expect(DynamicConfig::float('pricing.tax_rate_percent'))->toBe(7.5)
        ->and(DynamicConfig::boolean('feature.enabled'))->toBeTrue()
        ->and(DynamicConfig::integer('retry.count'))->toBe(3);
});

it('caches a miss so a missing key does not query on every read', function (): void {
    DynamicConfig::get('absent.key', 'default');

    ConfigEntry::query()->create([
        'key' => 'absent.key',
        'value' => 'now-present',
        'group' => 'misc',
        'label' => 'Absent key',
    ]);

    // Still the cached miss until the cache is cleared — the write path does that
    // for us, so only a direct model write can land here.
    expect(DynamicConfig::get('absent.key', 'default'))->toBe('default');

    DynamicConfig::forget('absent.key');

    expect(DynamicConfig::get('absent.key', 'default'))->toBe('now-present');
});
