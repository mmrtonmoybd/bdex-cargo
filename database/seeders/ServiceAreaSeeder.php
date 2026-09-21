<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ServiceArea;
use Illuminate\Database\Seeder;

/**
 * Coverage, checked at booking time so an out-of-area address is caught before
 * anyone pays.
 */
final class ServiceAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['AU', 'Sydney', 'NSW', ['2000', '20', '21', '22'], 10, 5],
            ['AU', 'Melbourne', 'VIC', ['30', '31', '32'], 10, 5],
            ['AU', 'Brisbane', 'QLD', ['40', '41'], 10, 5],
            ['AU', 'Perth', 'WA', ['60', '61'], 12, 6],
            ['AU', 'Adelaide', 'SA', ['50', '51'], 12, 6],
            ['AU', 'Canberra', 'ACT', ['26'], 11, 6],
            ['AU', 'Hobart', 'TAS', ['70'], 12, 7],
            ['BD', 'Dhaka', 'Dhaka', [], 9, 4],
            ['BD', 'Chittagong', 'Chattogram', [], 9, 5],
            ['BD', 'Sylhet', 'Sylhet', [], 9, 5],
            ['BD', 'Khulna', 'Khulna', [], 10, 6],
            ['BD', 'Rajshahi', 'Rajshahi', [], 10, 6],
        ];

        foreach ($areas as $index => [$country, $city, $region, $postcodes, $standard, $express]) {
            ServiceArea::query()->updateOrCreate(
                ['country' => $country, 'city' => $city],
                [
                    'region' => $region,
                    'postcodes' => $postcodes,
                    'supports_pickup' => true,
                    'supports_delivery' => true,
                    'standard_days' => $standard,
                    'express_days' => $express,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
