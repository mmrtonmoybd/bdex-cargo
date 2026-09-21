<?php

use App\Models\ItemCategory;
use App\Models\PricingZone;
use App\Models\ShippingRoute;
use App\Services\Pricing\PricingEngine;
use App\Services\Pricing\QuoteItem;
use App\Services\Pricing\QuoteResult;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The public price calculator.
 *
 * Every figure it shows comes from PricingEngine, which reads the same rows the
 * admin panel edits — so the page can never quote a rate the rate card does not
 * hold, and a price change is live without a deploy.
 */
new class extends Component
{
    public ?int $routeId = null;

    public ?int $zoneId = null;

    /** @var array<int, array{category_id: ?int, quantity: int, weight_kg: float, declared_value: float}> */
    public array $items = [];

    public int $boxCount = 1;

    public float $lightestBoxWeightKg = 20;

    public float $totalWeightKg = 5;

    public bool $isGarment = true;

    public ?string $error = null;

    public function mount(): void
    {
        $this->routeId = $this->routes->first()?->id;
        $this->syncRouteDefaults();
    }

    public function updatedRouteId(): void
    {
        $this->syncRouteDefaults();
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);

        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->addItem();
        }
    }

    #[Computed]
    public function routes(): \Illuminate\Support\Collection
    {
        return ShippingRoute::query()->active()->orderBy('sort_order')->get();
    }

    #[Computed]
    public function route(): ?ShippingRoute
    {
        return $this->routes->firstWhere('id', $this->routeId);
    }

    #[Computed]
    public function zones(): \Illuminate\Support\Collection
    {
        return PricingZone::query()
            ->where('shipping_route_id', $this->routeId)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function categories(): \Illuminate\Support\Collection
    {
        return ItemCategory::query()
            ->where('shipping_route_id', $this->routeId)
            ->active()
            ->with('zoneRates')
            ->orderBy('sort_order')
            ->get();
    }

    /** True when this route prices by item category rather than by total weight. */
    #[Computed]
    public function pricesByCategory(): bool
    {
        return $this->categories->isNotEmpty();
    }

    #[Computed]
    public function quote(): ?QuoteResult
    {
        $this->error = null;
        $route = $this->route;

        if ($route === null) {
            return null;
        }

        try {
            if ($this->pricesByCategory) {
                $zone = $this->zones->firstWhere('id', $this->zoneId);

                if ($zone === null) {
                    return null;
                }

                $items = collect($this->items)
                    ->map(fn (array $row): ?QuoteItem => $this->toQuoteItem($row))
                    ->filter()
                    ->values();

                if ($items->isEmpty()) {
                    return null;
                }

                return app(PricingEngine::class)->quoteAuToBd(
                    $items,
                    $zone,
                    max(1, $this->boxCount),
                    (float) $this->lightestBoxWeightKg,
                );
            }

            return app(PricingEngine::class)->quoteBdToAu(
                (float) $this->totalWeightKg,
                $this->isGarment,
                $route,
                max(1, $this->boxCount),
            );
        } catch (RuntimeException $exception) {
            // The engine refuses to invent a price when the rate card has a gap.
            // Saying so is more useful than showing a number nobody can honour.
            $this->error = $exception->getMessage();

            return null;
        }
    }

    private function toQuoteItem(array $row): ?QuoteItem
    {
        $category = $this->categories->firstWhere('id', (int) ($row['category_id'] ?? 0));

        if ($category === null) {
            return null;
        }

        return new QuoteItem(
            category: $category,
            quantity: max(1, (int) ($row['quantity'] ?? 1)),
            weightKg: max(0, (float) ($row['weight_kg'] ?? 0)),
            declaredValue: max(0, (float) ($row['declared_value'] ?? 0)),
        );
    }

    private function syncRouteDefaults(): void
    {
        unset($this->zones, $this->categories, $this->route, $this->pricesByCategory);

        $this->zoneId = $this->zones->first()?->id;
        $this->items = [$this->blankItem()];
    }

    /** @return array{category_id: ?int, quantity: int, weight_kg: float, declared_value: float} */
    private function blankItem(): array
    {
        return [
            'category_id' => $this->categories->first()?->id,
            'quantity' => 1,
            'weight_kg' => 1,
            'declared_value' => 0,
        ];
    }
};
?>

<div class="grid gap-8 lg:grid-cols-[1.4fr_1fr]">
    <div class="rounded-2xl border border-[var(--line)] bg-[var(--card)] p-6 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block">
                <span class="text-xs font-semibold tracking-widest text-[var(--muted)] uppercase">Route</span>
                <select wire:model.live="routeId" class="mt-2 w-full rounded-lg border border-[var(--line)] bg-white px-3 py-2">
                    @foreach ($this->routes as $route)
                        <option value="{{ $route->id }}">{{ $route->name }}</option>
                    @endforeach
                </select>
            </label>

            @if ($this->pricesByCategory)
                <label class="block">
                    <span class="text-xs font-semibold tracking-widest text-[var(--muted)] uppercase">Sending from</span>
                    <select wire:model.live="zoneId" class="mt-2 w-full rounded-lg border border-[var(--line)] bg-white px-3 py-2">
                        @foreach ($this->zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
        </div>

        @if ($this->pricesByCategory)
            <div class="mt-6 space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="item-{{ $index }}" class="grid gap-3 rounded-xl border border-[var(--line-soft)] p-3 sm:grid-cols-[2fr_auto_auto_auto_auto]">
                        <select wire:model.live="items.{{ $index }}.category_id" class="rounded-lg border border-[var(--line)] px-3 py-2">
                            @foreach ($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>

                        <label class="flex items-center gap-2 text-sm">
                            <span class="text-[var(--muted)]">Qty</span>
                            <input type="number" min="1" wire:model.live.debounce.400ms="items.{{ $index }}.quantity"
                                   class="w-16 rounded-lg border border-[var(--line)] px-2 py-2">
                        </label>

                        <label class="flex items-center gap-2 text-sm">
                            <span class="text-[var(--muted)]">kg</span>
                            <input type="number" min="0" step="0.1" wire:model.live.debounce.400ms="items.{{ $index }}.weight_kg"
                                   class="w-20 rounded-lg border border-[var(--line)] px-2 py-2">
                        </label>

                        <label class="flex items-center gap-2 text-sm">
                            <span class="text-[var(--muted)]">Value $</span>
                            <input type="number" min="0" step="1" wire:model.live.debounce.400ms="items.{{ $index }}.declared_value"
                                   class="w-24 rounded-lg border border-[var(--line)] px-2 py-2">
                        </label>

                        <button type="button" wire:click="removeItem({{ $index }})"
                                class="rounded-lg px-3 py-2 text-sm text-[var(--red)] hover:bg-[var(--line-soft)]">
                            Remove
                        </button>
                    </div>
                @endforeach

                <button type="button" wire:click="addItem"
                        class="rounded-lg border border-dashed border-[var(--line)] px-4 py-2 text-sm font-medium text-[var(--muted)] hover:border-[var(--sig)] hover:text-[var(--ink)]">
                    Add another item
                </button>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-xs font-semibold tracking-widest text-[var(--muted)] uppercase">Number of boxes</span>
                    <input type="number" min="1" wire:model.live.debounce.400ms="boxCount"
                           class="mt-2 w-full rounded-lg border border-[var(--line)] px-3 py-2">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold tracking-widest text-[var(--muted)] uppercase">Lightest box (kg)</span>
                    <input type="number" min="0" step="0.1" wire:model.live.debounce.400ms="lightestBoxWeightKg"
                           class="mt-2 w-full rounded-lg border border-[var(--line)] px-3 py-2">
                    <span class="mt-1 block text-xs text-[var(--muted)]">Boxes under 15kg dropped at Australia Post carry an extra fee.</span>
                </label>
            </div>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-xs font-semibold tracking-widest text-[var(--muted)] uppercase">Total weight (kg)</span>
                    <input type="number" min="0" step="0.1" wire:model.live.debounce.400ms="totalWeightKg"
                           class="mt-2 w-full rounded-lg border border-[var(--line)] px-3 py-2">
                </label>
                <label class="mt-6 flex items-center gap-3">
                    <input type="checkbox" wire:model.live="isGarment" class="size-4 rounded border-[var(--line)]">
                    <span class="text-sm">Garments only</span>
                </label>
            </div>
        @endif
    </div>

    <aside class="rounded-2xl border border-[var(--line)] bg-[var(--void)] p-6 text-white shadow-lg">
        <p class="font-mono text-xs tracking-widest text-[var(--sig)] uppercase">Your quote</p>

        @if ($error)
            <p class="mt-4 rounded-lg bg-white/10 p-3 text-sm text-[var(--soft)]">{{ $error }}</p>
        @elseif ($this->quote)
            @php($quote = $this->quote)

            <dl class="mt-4 space-y-2 text-sm">
                @foreach ($quote->lineItems as $line)
                    <div class="flex items-baseline justify-between gap-4">
                        <dt>
                            {{ $line->label }}
                            @if ($line->detail)
                                <span class="block text-xs text-white/50">{{ $line->detail }}</span>
                            @endif
                        </dt>
                        <dd class="font-mono whitespace-nowrap">${{ number_format($line->amount, 2) }}</dd>
                    </div>
                @endforeach

                @if ($quote->taxRatePercent > 0)
                    <div class="flex justify-between border-t border-white/10 pt-2">
                        <dt>Tax ({{ rtrim(rtrim(number_format($quote->taxRatePercent, 2), '0'), '.') }}%)</dt>
                        <dd class="font-mono">${{ number_format($quote->tax(), 2) }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-5 border-t border-white/20 pt-4">
                <p class="font-display text-3xl">${{ number_format($quote->total(), 2) }} <span class="text-base text-white/60">{{ $quote->baseCurrency }}</span></p>

                @if ($quote->convertedTotal() !== null)
                    <p class="mt-1 text-sm text-[var(--soft)]">
                        ≈ {{ number_format($quote->convertedTotal(), 0) }} {{ $quote->quoteCurrency }}
                        <span class="block text-xs text-white/40">Rate stored {{ \Illuminate\Support\Carbon::parse($quote->fxFetchedAt)->diffForHumans() }}</span>
                    </p>
                @endif
            </div>

            <p class="mt-4 text-xs text-white/50">Every line above traces back to a published rate or fee. Nothing is added at checkout.</p>
        @else
            <p class="mt-4 text-sm text-white/60">Choose what you are sending to see a price.</p>
        @endif
    </aside>
</div>
