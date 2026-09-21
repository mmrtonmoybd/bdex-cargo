<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\PricingModel;
use App\Models\ItemCategory;
use App\Models\PricingZone;
use App\Models\ShippingRoute;
use App\Models\SurchargeRule;
use App\Models\TvSizeBand;
use App\Models\WeightTier;
use App\Services\Config\DynamicConfig;
use Illuminate\Support\Collection;

/**
 * Everything the public page's scripts render from, in the shape the design expects.
 *
 * The prototype carried these arrays as literals. Here they are assembled from the
 * database — copy from content_blocks, the rate card and tier sheet from the
 * pricing tables — so the published rates and the calculator are the same data.
 */
final class PublicSitePayload
{
    public function __construct(
        private readonly SiteContent $content,
        private readonly SiteSettings $settings,
    ) {}

    /** @return array<string, mixed> */
    public function build(): array
    {
        $auToBd = ShippingRoute::query()->where('code', 'AU_TO_BD')->first();
        $bdToAu = ShippingRoute::query()->where('code', 'BD_TO_AU')->first();

        return [
            'nav' => $this->content->data('nav', 'links', []),
            'chips' => $this->content->data('hero', 'chips', []),
            'marquee' => $this->content->data('marquee', 'items', []),
            'slideLabels' => $this->content->data('hero', 'slideLabels', []),
            'stats' => $this->content->data('stats', 'cells', []),
            'aboutTags' => $this->content->data('about', 'tags', []),
            'assurances' => $this->content->data('about', 'assurances', []),
            'solutions' => $this->content->data('solutions', 'cards', []),
            'benefits' => $this->content->data('services', 'benefits', []),
            'perks' => $this->content->data('booking', 'perks', []),
            'journey' => $this->content->data('journey', 'steps', []),
            'dropRules' => $this->content->data('dropoff', 'rules', []),
            'labelFields' => $this->content->data('dropoff', 'labelFields', []),
            'terms' => $this->content->data('terms', 'items', []),
            'why' => $this->content->data('why', 'items', []),
            'quotes' => $this->content->data('testimonials', 'quotes', []),
            'faqs' => $this->content->data('faq', 'items', []),
            'footerColumns' => $this->content->data('footer', 'columns', []),

            'zones' => $this->zones($auToBd),
            'cats' => $this->categories($auToBd),
            'bdTiers' => $this->weightTiers($bdToAu),
            'ausbdRows' => $this->rateCardRows($auToBd),
            'tvCards' => $this->tvCards(),
            'tvTax' => $this->tvTaxBands(),

            'routes' => [
                'ausbd' => [
                    'id' => $auToBd?->id,
                    'name' => $auToBd?->name ?? 'Australia → Bangladesh',
                    'transit' => $auToBd?->transit_time_text,
                ],
                'bdaus' => [
                    'id' => $bdToAu?->id,
                    'name' => $bdToAu?->name ?? 'Bangladesh → Australia',
                    'transit' => $bdToAu?->transit_time_text,
                ],
            ],

            'phoneAu' => (string) $this->settings->get('contact_phone_au'),
            'phoneBd' => (string) $this->settings->get('contact_phone_bd'),

            'copy' => [
                'trackEmpty' => 'Enter your BD-EX reference to see where the parcel is.',
                'trackMissing' => 'No parcel found for that number. Check the reference on your booking confirmation.',
                'bookingFailed' => 'We could not save that booking. Check the details and try again.',
            ],

            'endpoints' => [
                'quote' => route('api.quote.preview'),
                'track' => route('api.track.index').'/',
                'booking' => route('api.booking.store'),
            ],

            'csrf' => csrf_token(),
        ];
    }

    /** @return array<int, array{id: string, label: string}> */
    private function zones(?ShippingRoute $route): array
    {
        if ($route === null) {
            return [];
        }

        return $route->pricingZones()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PricingZone $zone): array => [
                'id' => (string) $zone->id,
                'label' => $zone->description !== null && $zone->name !== $zone->description
                    ? strtoupper(str_replace(', ', ' · ', $zone->description))
                    : $zone->name,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function categories(?ShippingRoute $route): array
    {
        if ($route === null) {
            return [];
        }

        return ItemCategory::query()
            ->where('shipping_route_id', $route->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ItemCategory $category): array => [
                'id' => (string) $category->id,
                'label' => $category->description === null
                    ? $category->name
                    : $category->name.' — '.$category->description,
                'type' => $category->pricing_model === PricingModel::PerKgByZone ? 'kg' : 'flat',
            ])
            ->all();
    }

    /** @return array<int, array{w: string, rate: float}> */
    private function weightTiers(?ShippingRoute $route): array
    {
        if ($route === null) {
            return [];
        }

        return WeightTier::query()
            ->where('shipping_route_id', $route->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (WeightTier $tier): array => [
                'w' => $this->tierLabel($tier),
                'rate' => (float) $tier->price_per_kg,
            ])
            ->all();
    }

    private function tierLabel(WeightTier $tier): string
    {
        $min = $this->trim($tier->min_kg);

        if ($tier->max_kg === null) {
            return $min.'+';
        }

        $max = $this->trim($tier->max_kg);

        // A band that starts and ends on the same whole number reads better as one
        // figure — which is how the published sheet prints it.
        return $min === $max || (float) $tier->min_kg > (float) $tier->max_kg - 1
            ? $max
            : $min.' – '.$max;
    }

    /** @return array<int, array<string, string>> */
    private function rateCardRows(?ShippingRoute $route): array
    {
        if ($route === null) {
            return [];
        }

        $zones = $route->pricingZones()->where('is_active', true)->orderBy('sort_order')->get();
        $extras = SurchargeRule::query()
            ->where('is_active', true)
            ->whereNotNull('item_category_id')
            ->get()
            ->groupBy('item_category_id');

        return ItemCategory::query()
            ->where('shipping_route_id', $route->id)
            ->where('is_active', true)
            ->with('zoneRates')
            ->orderBy('sort_order')
            ->get()
            ->map(function (ItemCategory $category) use ($zones, $extras): array {
                $note = collect([$category->description])
                    ->merge(
                        $extras->get($category->id, collect())
                            ->map(fn (SurchargeRule $rule): string => $rule->label)
                    )
                    ->filter()
                    ->implode(' · ');

                $values = $zones->map(fn (PricingZone $zone): string => $this->rateLabel($category, $zone));

                return [
                    'name' => $category->name,
                    'note' => $note,
                    'nsw' => $values->first() ?? '—',
                    'other' => $values->get(1, $values->first() ?? '—'),
                ];
            })
            ->all();
    }

    private function rateLabel(ItemCategory $category, PricingZone $zone): string
    {
        if ($category->pricing_model === PricingModel::PerKgByZone) {
            $rate = $category->rateForZone($zone);

            return $rate === null ? '—' : '$'.$this->trim((string) $rate).'/kg';
        }

        return '$'.$this->trim((string) $category->flat_fee_amount);
    }

    /** @return array<int, array{size: string, lines: array<int, array<int, string>>}> */
    private function tvCards(): array
    {
        $perKg = DynamicConfig::float('pricing.tv.per_kg_rate_aud');
        $airport = DynamicConfig::float('pricing.tv.airport_processing_fee_aud');

        return TvSizeBand::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (TvSizeBand $band): array => [
                'size' => $band->size_label,
                'lines' => [
                    ['Freight', '$'.$this->trim((string) $perKg).'/kg'],
                    ['Pallet weight', $this->trim($band->default_pallet_weight_kg).' kg'],
                    ['Airport processing', '$'.$this->trim((string) $airport)],
                    ['Volume fee', '$'.$this->trim($band->volume_fee_aud)],
                    ['BD govt duty', 'BDT '.number_format((float) $band->bd_govt_duty_bdt)],
                    ['Local home delivery', 'BDT '.number_format((float) $band->local_delivery_bdt)],
                ],
            ])
            ->all();
    }

    /** @return array<int, array<int, string>> */
    private function tvTaxBands(): array
    {
        return TvSizeBand::query()
            ->orderBy('min_inches')
            ->get()
            ->map(fn (TvSizeBand $band): array => [
                $band->max_inches === null
                    ? $band->min_inches.'"+'
                    : $band->min_inches.'"–'.$band->max_inches.'"',
                'BDT '.number_format((float) $band->bd_govt_duty_bdt),
            ])
            ->unique(fn (array $row): string => $row[0])
            ->values()
            ->all();
    }

    private function trim(string|float $value): string
    {
        $text = is_float($value) ? number_format($value, 3, '.', '') : $value;

        return str_contains($text, '.') ? rtrim(rtrim($text, '0'), '.') : $text;
    }

    /** @return Collection<int, mixed> */
    private function collect(mixed $value): Collection
    {
        return collect($value);
    }
}
