<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Concerns\InvalidatesSiteCache;
use Illuminate\Support\Facades\Cache;

/**
 * Cache keys for the public site's display configuration.
 *
 * Kept in one place so {@see InvalidatesSiteCache} can clear
 * everything an admin edit could have staled, without each model knowing which
 * keys the others own.
 */
final class SiteCache
{
    public const SETTINGS = 'site-settings';

    public const CONTENT_BLOCKS = 'content-blocks';

    public static function flush(): void
    {
        Cache::forget(self::SETTINGS);

        // Copy is cached per locale; clearing one locale would leave the other stale.
        foreach (array_keys((array) config('app.supported_locales', ['en' => 'English', 'bn' => 'বাংলা'])) as $locale) {
            Cache::forget(self::CONTENT_BLOCKS.":{$locale}");
        }

        Cache::forget(self::CONTENT_BLOCKS);
    }
}
