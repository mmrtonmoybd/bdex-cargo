<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\PricingModel;
use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Models\PricingZone;
use App\Models\ShippingRoute;
use App\Services\Pricing\PricingEngine;
use App\Services\Pricing\QuoteItem;
use App\Services\Pricing\QuoteResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * The live figure behind the public calculator.
 *
 * The page sends what the visitor selected; the engine decides what it costs.
 * No rate is computed in the browser, so the calculator and the admin rate card
 * cannot drift apart.
 */
final class QuotePreviewController extends Controller
{
    public function __invoke(Request $request, PricingEngine $engine): JsonResponse
    {
        $data = $request->validate([
            'direction' => ['required', 'in:ausbd,bdaus'],
            'zone' => ['nullable'],
            'category' => ['nullable'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'item_type' => ['nullable', 'in:garments,non-garments'],
        ]);

        $weight = max(0.5, (float) ($data['weight_kg'] ?? 0));

        try {
            $quote = $data['direction'] === 'ausbd'
                ? $this->auToBd($engine, $data, $weight)
                : $this->bdToAu($engine, $data, $weight);
        } catch (RuntimeException $exception) {
            // The engine refuses to guess where the rate card is silent. Say so
            // rather than showing a number nobody can honour.
            return response()->json([
                'total' => 0,
                'lines' => [],
                'eta' => '',
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'total' => $quote['result']->total(),
            'lines' => $quote['lines'],
            'eta' => $quote['eta'],
            'currency' => $quote['result']->baseCurrency,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{result: QuoteResult, lines: array<int, array<int, string>>, eta: string}
     */
    private function auToBd(PricingEngine $engine, array $data, float $weight): array
    {
        $zone = PricingZone::query()->findOrFail((int) $data['zone']);
        $category = ItemCategory::query()->with('zoneRates')->findOrFail((int) $data['category']);

        $quantity = 1;
        $itemWeight = $category->pricing_model === PricingModel::PerKgByZone ? $weight : 0.0;

        $result = $engine->quoteAuToBd(
            collect([new QuoteItem($category, $quantity, $itemWeight)]),
            $zone,
        );

        $route = $zone->shippingRoute;

        return [
            'result' => $result,
            'lines' => $this->lines($result),
            'eta' => trim(($route?->transit_time_text ?? '').' · '.$zone->name.' → Bangladesh', ' ·'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{result: QuoteResult, lines: array<int, array<int, string>>, eta: string}
     */
    private function bdToAu(PricingEngine $engine, array $data, float $weight): array
    {
        $route = ShippingRoute::query()->where('code', 'BD_TO_AU')->firstOrFail();

        $result = $engine->quoteBdToAu(
            $weight,
            ($data['item_type'] ?? 'garments') === 'garments',
            $route,
        );

        return [
            'result' => $result,
            'lines' => $this->lines($result),
            'eta' => trim(($route->transit_time_text ?? '').' · Bangladesh → anywhere in Australia', ' ·'),
        ];
    }

    /**
     * The design's two-column itemisation: what it is, what it costs.
     *
     * @return array<int, array<int, string>>
     */
    private function lines(QuoteResult $result): array
    {
        $lines = $result->lineItems
            ->map(fn ($line): array => [
                $line->detail === null ? $line->label : $line->label.' · '.$line->detail,
                '$'.number_format($line->amount, 2),
            ])
            ->all();

        if ($result->taxRatePercent > 0) {
            $lines[] = ['Tax', '$'.number_format($result->tax(), 2)];
        }

        return $lines;
    }
}
