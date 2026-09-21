<x-layouts.public>
    <section class="shell py-16">
        <p class="font-mono text-xs tracking-[0.25em] text-[var(--em)] uppercase">Instant quote</p>
        <h1 class="font-display mt-3 text-4xl font-bold">What will it cost?</h1>
        <p class="mt-2 max-w-2xl text-[var(--muted)]">
            Prices come straight from the published rate card. Every line is itemised, and nothing is added later.
        </p>

        <div class="mt-10">
            <livewire:quote.public-quote-calculator />
        </div>
    </section>
</x-layouts.public>
