<?php

declare(strict_types=1);

namespace App\Services\Compliance;

use App\Models\ServiceArea;
use Illuminate\Support\Collection;

/**
 * Checks an address against published coverage before a booking is taken.
 *
 * A country we do not serve is a hard no — there is no point collecting a parcel
 * we cannot deliver. A city or postcode we cannot match is only a warning: people
 * write "Dhaka-1207", "Sydney NSW" and "Gulshan" for addresses we serve perfectly
 * well, and refusing those over a spelling difference loses real business. The
 * mismatch is recorded for staff instead.
 */
final class ServiceAreaChecker
{
    public function servesCountry(string $country): bool
    {
        return ServiceArea::query()
            ->active()
            ->where('country', strtoupper($country))
            ->exists();
    }

    /** The covering area, or null when nothing matched. */
    public function locate(string $country, ?string $city, ?string $postcode = null): ?ServiceArea
    {
        $areas = $this->areasFor($country);

        if ($city !== null && $city !== '') {
            $needle = mb_strtolower($city);

            $matched = $areas->first(function (ServiceArea $area) use ($needle, $postcode): bool {
                $name = mb_strtolower($area->city);

                return (str_contains($needle, $name) || str_contains($name, $needle))
                    && $area->covers($postcode);
            });

            if ($matched !== null) {
                return $matched;
            }
        }

        if ($postcode === null || $postcode === '') {
            return null;
        }

        return $areas->first(fn (ServiceArea $area): bool => $area->covers($postcode));
    }

    /**
     * Transit estimate for a delivery address, in days.
     */
    public function transitDays(string $country, ?string $city, bool $express = false): ?int
    {
        $area = $this->locate($country, $city);

        return $express ? $area?->express_days : $area?->standard_days;
    }

    /** @return Collection<int, ServiceArea> */
    private function areasFor(string $country): Collection
    {
        return ServiceArea::query()
            ->active()
            ->where('country', strtoupper($country))
            ->orderBy('sort_order')
            ->get();
    }
}
