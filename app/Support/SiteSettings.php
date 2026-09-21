<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Concerns\InvalidatesSiteCache;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Read access to the single site_settings row.
 *
 * Nothing secret passes through here, so the whole row is cached and served to
 * every public request. The cache is cleared on save by
 * {@see InvalidatesSiteCache}, not by a queued job — a colour
 * change must be visible on the very next page load.
 *
 * Attributes are cached, never the model object: a serialised Eloquent instance
 * comes back as an incomplete class if the cache outlives the class definition,
 * and a plain array cannot.
 */
final class SiteSettings
{
    public function model(): SiteSetting
    {
        return (new SiteSetting)->forceFill($this->attributes());
    }

    /** @return array<string, mixed> */
    public function attributes(): array
    {
        if (! Schema::hasTable('site_settings')) {
            return [];
        }

        /** @var array<string, mixed> $attributes */
        $attributes = Cache::rememberForever(
            SiteCache::SETTINGS,
            static fn (): array => SiteSetting::query()->firstOrNew([])->attributesToArray()
        );

        return $attributes;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes()[$key] ?? $default;
    }

    /** @return array<string, string> CSS custom property name => value. */
    public function themeTokens(): array
    {
        return [
            '--void' => (string) $this->get('color_void'),
            '--sig' => (string) $this->get('color_signal'),
            '--red' => (string) $this->get('color_red'),
            '--blue' => (string) $this->get('color_blue'),
            '--bg' => (string) $this->get('color_bg'),
            '--ink' => (string) $this->get('color_ink'),
            '--nav-bg' => (string) $this->get('color_nav_bg', '#eaeeec'),
            '--nav-ink' => (string) $this->get('color_nav_ink', '#0b1f17'),
            '--disp' => (string) $this->get('font_display'),
            '--body' => (string) $this->get('font_body'),
            '--mono' => (string) $this->get('font_mono'),
        ];
    }

    /** @return array<int, string> */
    public function bannedItems(): array
    {
        $items = $this->get('banned_items', []);

        return is_array($items) ? $items : (array) json_decode((string) $items, true);
    }
}
