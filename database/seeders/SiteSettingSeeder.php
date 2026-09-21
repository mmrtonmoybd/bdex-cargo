<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * Brand defaults, taken from the reference prototype's :root block.
 *
 * These are starting values, not constants — every one is editable from the
 * Settings page and reaches the public site as a CSS custom property.
 */
final class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::query()->firstOrCreate([], [
            'business_name' => 'BD-EX Cargo',
            'tagline' => 'Australia ⇄ Bangladesh parcel and cargo, tracked end to end.',
            'color_void' => '#04150f',
            'color_signal' => '#25e39a',
            'color_red' => '#c8102e',
            'color_blue' => '#12457f',
            'color_bg' => '#f5f8f6',
            'color_ink' => '#08160f',
            'banned_items' => [
                'perfume',
                'body spray',
                'flammable',
                'alcohol',
                'cigarette',
                'gold',
                'diamond',
                'currency',
                'cash',
                'AA battery',
                'AAA battery',
                'adult toy',
            ],
        ]);
    }
}
