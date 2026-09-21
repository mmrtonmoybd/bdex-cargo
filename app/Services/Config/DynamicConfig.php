<?php

declare(strict_types=1);

namespace App\Services\Config;

use App\Models\ConfigEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * The single read path for runtime configuration.
 *
 * Every third-party credential, base URL, driver selection, feature toggle and
 * operational number is a `config_entries` row reached through here — never an
 * `env()` call, never a `config('services.*')` entry. Only the bootstrap set
 * Laravel needs to reach the database at all (APP_KEY, APP_ENV, APP_URL,
 * APP_DEBUG, DB_*, CACHE_STORE, QUEUE_CONNECTION, SESSION_DRIVER) stays in .env;
 * a Pest test enforces that boundary against app/.
 *
 * Rotating a credential is therefore a Filament edit that takes effect on the
 * next request. Onboarding a provider nobody has written a driver class for is
 * the one thing this cannot do — that needs one new PHP class implementing the
 * relevant interface, once, after which every operational change is dynamic again.
 */
final class DynamicConfig
{
    private const CACHE_PREFIX = 'dynamic-config:';

    public static function get(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('config_entries')) {
            return $default;
        }

        $value = Cache::rememberForever(self::cacheKey($key), static function () use ($key): array {
            $row = ConfigEntry::query()->where('key', $key)->first();

            if ($row === null) {
                return ['missing' => true, 'value' => null];
            }

            return ['missing' => false, 'value' => $row->plainValue()];
        });

        return $value['missing'] === true ? $default : ($value['value'] ?? $default);
    }

    public static function float(string $key, float $default = 0.0): float
    {
        $value = self::get($key);

        return is_numeric($value) ? (float) $value : $default;
    }

    public static function integer(string $key, int $default = 0): int
    {
        $value = self::get($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    public static function boolean(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOL);
    }

    public static function set(string $key, mixed $value, bool $isSecret = false): void
    {
        $entry = ConfigEntry::query()->firstOrNew(['key' => $key]);

        $entry->is_secret = $isSecret;
        $entry->fillPlainValue($value === null ? null : (string) $value);

        $entry->group ??= 'misc';
        $entry->label ??= $key;
        $entry->save();

        self::forget($key);
    }

    /**
     * Declares a credential slot without setting a value.
     *
     * Lets a seeder publish the fields a provider needs — so they appear on the
     * Configuration page ready to fill — without ever shipping a secret in code.
     * An existing value is left untouched.
     */
    public static function defineSecret(
        string $key,
        string $group,
        string $label,
        ?string $description = null,
        int $sortOrder = 0,
    ): void {
        $entry = ConfigEntry::query()->firstOrNew(['key' => $key]);

        $entry->fill([
            'is_secret' => true,
            'group' => $group,
            'label' => $label,
            'description' => $description,
            'sort_order' => $sortOrder,
        ]);

        $entry->save();

        self::forget($key);
    }

    public static function forget(string $key): void
    {
        Cache::forget(self::cacheKey($key));
    }

    /** Clears every cached entry. Used after bulk edits on the Integrations page. */
    public static function flush(): void
    {
        if (! Schema::hasTable('config_entries')) {
            return;
        }

        ConfigEntry::query()->pluck('key')->each(static fn (string $key) => self::forget($key));
    }

    private static function cacheKey(string $key): string
    {
        return self::CACHE_PREFIX.$key;
    }
}
