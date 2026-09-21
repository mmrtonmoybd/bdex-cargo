<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\SiteCache;

/**
 * Busts the public site's caches whenever the model changes.
 *
 * One mechanism for every display-config model, so nothing grows a bespoke
 * cache-bust of its own and no admin edit needs a manual cache clear.
 */
trait InvalidatesSiteCache
{
    public static function bootInvalidatesSiteCache(): void
    {
        $flush = static fn (): mixed => SiteCache::flush();

        static::saved($flush);
        static::deleted($flush);
    }
}
