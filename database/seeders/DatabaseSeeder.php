<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ConfigEntrySeeder::class,
            NotificationConfigSeeder::class,
            SiteSettingSeeder::class,
            ContentBlockSeeder::class,
            ServiceAreaSeeder::class,
            PricingSeeder::class,
            CarrierSeeder::class,
        ]);

        // PricingSeeder deliberately leaves the figures the rate card never stated
        // blank and flagged. DemoDataSeeder is the one allowed to invent them, and
        // running it here is what makes a freshly seeded system usable end to end.
        if ($this->command?->option('class') === null || app()->environment('local')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
