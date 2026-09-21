<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Models\PricingZone;
use App\Models\ShippingRoute;
use App\Models\WeightTier;
use App\Support\SiteContent;
use Illuminate\Contracts\View\View;

final class HomeController extends Controller
{
    public function __invoke(SiteContent $content): View
    {
        $routes = ShippingRoute::query()->active()->orderBy('sort_order')->get();

        return view('public.home', [
            'content' => $content,
            'routes' => $routes,
            'zones' => PricingZone::query()->active()->orderBy('sort_order')->get(),
            'categories' => ItemCategory::query()
                ->active()
                ->with('zoneRates')
                ->orderBy('sort_order')
                ->get(),
            'weightTiers' => WeightTier::query()->active()->orderBy('sort_order')->get(),
        ]);
    }
}
