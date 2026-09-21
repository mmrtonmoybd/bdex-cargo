<x-layouts.public>
    {{-- Hero --}}
    <section class="bg-[var(--void)] text-white">
        <div class="shell grid gap-10 py-20 lg:grid-cols-[1.2fr_1fr] lg:py-28">
            <div>
                <p class="font-mono text-xs tracking-[0.25em] text-[var(--sig)] uppercase">
                    Fast · Affordable · Hassle-free
                </p>

                <h1 class="font-display mt-5 text-4xl leading-[1.05] font-extrabold sm:text-5xl lg:text-6xl">
                    {{ $content->title('hero', 'Parcels between Australia and Bangladesh') }}
                </h1>

                <p class="mt-6 max-w-xl text-lg text-white/70">
                    {{ $content->body('hero') }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('quote') }}"
                       class="rounded-full bg-[var(--sig)] px-6 py-3 font-semibold text-[var(--void)]">
                        Get an instant price
                    </a>
                    <a href="#rates"
                       class="rounded-full border border-white/25 px-6 py-3 font-semibold hover:border-[var(--sig)]">
                        See the rate card
                    </a>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-4 self-end">
                @foreach ($routes as $route)
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <dt class="font-mono text-xs tracking-widest text-[var(--sig)] uppercase">
                            {{ $route->origin_country }} → {{ $route->destination_country }}
                        </dt>
                        <dd class="mt-2 text-sm text-white/80">
                            {{ $route->transit_time_text ?? 'Transit time on request' }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- About / services / solutions --}}
    <section id="about" class="shell py-20">
        <div class="grid gap-12 lg:grid-cols-3">
            @foreach (['about', 'solutions', 'services'] as $key)
                @if ($block = $content->section($key))
                    <article>
                        <h2 class="font-display text-2xl font-bold">{{ $block->title }}</h2>
                        <p class="mt-3 text-[var(--muted)]">{{ $block->body }}</p>
                    </article>
                @endif
            @endforeach
        </div>
    </section>

    {{-- Instant quote --}}
    <section id="calculator" class="bg-[var(--card)] py-20">
        <div class="shell">
            <p class="font-mono text-xs tracking-[0.25em] text-[var(--em)] uppercase">Price calculator</p>
            <h2 class="font-display mt-3 text-3xl font-bold">{{ $content->title('calculator', 'Price calculator') }}</h2>
            <p class="mt-2 max-w-2xl text-[var(--muted)]">{{ $content->body('calculator') }}</p>

            <div class="mt-10">
                <livewire:quote.public-quote-calculator />
            </div>
        </div>
    </section>

    {{-- Rate card --}}
    <section id="rates" class="shell py-20">
        <p class="font-mono text-xs tracking-[0.25em] text-[var(--em)] uppercase">Rate card</p>
        <h2 class="font-display mt-3 text-3xl font-bold">{{ $content->title('rates', 'Transparent pricing') }}</h2>
        <p class="mt-2 max-w-2xl text-[var(--muted)]">{{ $content->body('rates') }}</p>

        @php($zonedCategories = $categories->where('pricing_model', App\Enums\PricingModel::PerKgByZone))

        @if ($zonedCategories->isNotEmpty())
            <div class="mt-8 overflow-x-auto rounded-2xl border border-[var(--line)]">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead class="bg-[var(--line-soft)] text-xs tracking-widest uppercase">
                        <tr>
                            <th class="px-4 py-3">Item category</th>
                            @foreach ($zones as $zone)
                                <th class="px-4 py-3">{{ $zone->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--line-soft)]">
                        @foreach ($zonedCategories as $category)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="font-medium">{{ $category->name }}</span>
                                    @if ($category->description)
                                        <span class="mt-0.5 block text-xs text-[var(--muted)]">{{ $category->description }}</span>
                                    @endif
                                </td>
                                @foreach ($zones as $zone)
                                    <td class="px-4 py-3 font-mono">
                                        @php($rate = $category->rateForZone($zone))
                                        {{ $rate === null ? '—' : '$'.number_format($rate, 2).'/kg' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @php($flatCategories = $categories->where('pricing_model', '!=', App\Enums\PricingModel::PerKgByZone))

        @if ($flatCategories->isNotEmpty())
            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($flatCategories as $category)
                    <div class="rounded-xl border border-[var(--line)] bg-[var(--card)] p-4">
                        <p class="font-medium">{{ $category->name }}</p>
                        <p class="font-mono text-lg">${{ number_format((float) $category->flat_fee_amount, 0) }}
                            <span class="text-xs text-[var(--muted)]">
                                {{ $category->pricing_model === App\Enums\PricingModel::FlatFeePerItem ? 'per piece' : 'flat' }}
                            </span>
                        </p>
                        @if ($category->max_weight_kg)
                            <p class="text-xs text-[var(--muted)]">Up to {{ rtrim(rtrim($category->max_weight_kg, '0'), '.') }}kg</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($weightTiers->isNotEmpty())
            <div class="mt-10">
                <h3 class="font-display text-xl font-bold">Priced by total weight</h3>
                <div class="mt-4 overflow-x-auto rounded-2xl border border-[var(--line)]">
                    <table class="w-full min-w-[24rem] text-left text-sm">
                        <thead class="bg-[var(--line-soft)] text-xs tracking-widest uppercase">
                            <tr>
                                <th class="px-4 py-3">Weight</th>
                                <th class="px-4 py-3">Rate per kg</th>
                                <th class="px-4 py-3">Applies to</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--line-soft)]">
                            @foreach ($weightTiers as $tier)
                                <tr>
                                    <td class="px-4 py-3 font-mono">
                                        {{ rtrim(rtrim($tier->min_kg, '0'), '.') }}–{{ $tier->max_kg ? rtrim(rtrim($tier->max_kg, '0'), '.') : '∞' }}kg
                                    </td>
                                    <td class="px-4 py-3 font-mono">${{ number_format((float) $tier->price_per_kg, 2) }}</td>
                                    <td class="px-4 py-3 text-[var(--muted)]">{{ $tier->applies_to->getLabel() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>

    {{-- Journey, drop-off, terms, FAQ --}}
    <section class="bg-[var(--card)] py-20">
        <div class="shell grid gap-12 lg:grid-cols-2">
            @foreach (['journey', 'dropoff', 'terms', 'faq'] as $key)
                @if ($block = $content->section($key))
                    <article id="{{ $key }}">
                        <h2 class="font-display text-2xl font-bold">{{ $block->title }}</h2>
                        <p class="mt-3 text-[var(--muted)]">{{ $block->body }}</p>
                    </article>
                @endif
            @endforeach
        </div>
    </section>
</x-layouts.public>
