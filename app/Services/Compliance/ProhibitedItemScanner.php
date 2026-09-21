<?php

declare(strict_types=1);

namespace App\Services\Compliance;

use App\Support\SiteSettings;

/**
 * Checks a packing-list description against the editable banned-items list.
 *
 * Flags rather than blocks, deliberately. "Body butter" is not body spray, and a
 * keyword match is not proof — so a hit raises the item for staff review instead
 * of refusing a booking a human would have accepted. The list itself is a
 * repeater on the Settings page, so customs changing its mind is an admin edit.
 */
final class ProhibitedItemScanner
{
    public function __construct(private readonly SiteSettings $settings) {}

    /**
     * The first banned term found in the text, or null.
     */
    public function scan(string $description): ?string
    {
        $haystack = mb_strtolower($description);

        foreach ($this->settings->bannedItems() as $term) {
            $needle = mb_strtolower(trim((string) $term));

            if ($needle !== '' && str_contains($haystack, $needle)) {
                return (string) $term;
            }
        }

        return null;
    }

    /**
     * Every banned term found across a set of descriptions.
     *
     * @param  array<int, string>  $descriptions
     * @return array<int, string>
     */
    public function scanAll(array $descriptions): array
    {
        return collect($descriptions)
            ->map(fn (string $description): ?string => $this->scan($description))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
