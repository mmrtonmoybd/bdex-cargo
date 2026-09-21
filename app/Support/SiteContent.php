<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Concerns\InvalidatesSiteCache;
use App\Models\ContentBlock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Cached access to the public site's editable copy.
 *
 * Shares the cache key that {@see InvalidatesSiteCache} clears,
 * so a copy edit is visible on the next request with no separate invalidation.
 *
 * Like {@see SiteSettings}, this caches attribute arrays rather than models —
 * serialised Eloquent instances do not survive every cache driver intact.
 */
final class SiteContent
{
    /** @return Collection<string, ContentBlock> */
    public function all(?string $locale = null): Collection
    {
        if (! Schema::hasTable('content_blocks')) {
            return collect();
        }

        $locale ??= app()->getLocale();

        /** @var array<string, array<string, mixed>> $rows */
        $rows = Cache::rememberForever(
            SiteCache::CONTENT_BLOCKS.":{$locale}",
            static fn (): array => ContentBlock::query()
                ->active()
                ->where('locale', $locale)
                ->orderBy('sort_order')
                ->get()
                ->keyBy('section_key')
                ->map(fn (ContentBlock $block): array => $block->attributesToArray())
                ->all()
        );

        return collect($rows)->map(fn (array $attributes): ContentBlock => (new ContentBlock)->forceFill($attributes));
    }

    public function section(string $key): ?ContentBlock
    {
        return $this->all()->get($key);
    }

    public function title(string $key, string $default = ''): string
    {
        return $this->section($key)?->title ?? $default;
    }

    public function body(string $key, string $default = ''): string
    {
        return $this->section($key)?->body ?? $default;
    }
}
