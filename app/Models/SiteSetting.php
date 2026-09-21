<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\InvalidatesSiteCache;
use App\Support\SiteSettings;
use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Single-row display configuration for the public site.
 *
 * Read through {@see SiteSettings}, never directly — the repository
 * owns the cache and the "there is exactly one row" invariant.
 *
 * @property string $business_name
 * @property string|null $tagline
 * @property string|null $logo_path
 * @property string|null $favicon_path
 * @property string $color_void
 * @property string $color_signal
 * @property string $color_red
 * @property string $color_blue
 * @property string $color_bg
 * @property string $color_ink
 * @property string $font_display
 * @property string $font_body
 * @property string $font_mono
 * @property array<int, string>|null $banned_items
 */
final class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    use InvalidatesSiteCache;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'banned_items' => 'array',
        ];
    }
}
