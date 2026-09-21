<?php

use App\Models\TvSizeBand;
use App\Services\Pricing\TvCostCalculator;
use App\Services\Pricing\TvQuoteResult;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Landed cost for sending a television.
 *
 * The two sides of the cost stay in their own currencies: freight is billed in
 * AUD, Bangladesh duty and home delivery in BDT. Only a stored exchange rate lets
 * the page offer a single combined figure.
 */
new class extends Component
{
    public ?int $bandId = null;

    public ?float $palletWeightKg = null;

    public function mount(): void
    {
        $band = $this->bands->first();

        $this->bandId = $band?->id;
        $this->palletWeightKg = $band === null ? null : (float) $band->default_pallet_weight_kg;
    }

    public function updatedBandId(): void
    {
        unset($this->band);

        $this->palletWeightKg = $this->band === null ? null : (float) $this->band->default_pallet_weight_kg;
    }

    #[Computed]
    public function bands(): \Illuminate\Support\Collection
    {
        return TvSizeBand::query()->active()->orderBy('sort_order')->get();
    }

    #[Computed]
    public function band(): ?TvSizeBand
    {
        return $this->bands->firstWhere('id', $this->bandId);
    }

    #[Computed]
    public function result(): ?TvQuoteResult
    {
        return $this->band === null
            ? null
            : app(TvCostCalculator::class)->calculate($this->band, $this->palletWeightKg);
    }
};
?>

<div class="grid gap-8 lg:grid-cols-[1fr_1fr]">
    <div class="rounded-2xl border border-[var(--line)] bg-[var(--card)] p-6 shadow-sm">
        @if ($this->bands->isEmpty())
            <p class="text-sm text-[var(--muted)]">No screen sizes are published yet.</p>
        @else
            <label class="block">
                <span class="text-xs font-semibold tracking-widest text-[var(--muted)] uppercase">Screen size</span>
                <select wire:model.live="bandId" class="mt-2 w-full rounded-lg border border-[var(--line)] bg-white px-3 py-2">
                    @foreach ($this->bands as $band)
                        <option value="{{ $band->id }}">{{ $band->size_label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="mt-4 block">
                <span class="text-xs font-semibold tracking-widest text-[var(--muted)] uppercase">Pallet weight (kg)</span>
                <input type="number" min="0" step="0.5" wire:model.live.debounce.400ms="palletWeightKg"
                       class="mt-2 w-full rounded-lg border border-[var(--line)] px-3 py-2">
                <span class="mt-1 block text-xs text-[var(--muted)]">Crated TVs typically land between 10kg and 15kg.</span>
            </label>
        @endif
    </div>

    <aside class="rounded-2xl border border-[var(--line)] bg-[var(--void)] p-6 text-white shadow-lg">
        <p class="font-mono text-xs tracking-widest text-[var(--sig)] uppercase">Landed cost</p>

        @if ($this->result)
            @php($result = $this->result)

            <p class="mt-4 text-xs tracking-widest text-white/40 uppercase">Paid in Australia</p>
            <dl class="mt-2 space-y-1 text-sm">
                @foreach ($result->audLineItems as $line)
                    <div class="flex justify-between gap-4">
                        <dt>{{ $line->label }}</dt>
                        <dd class="font-mono">${{ number_format($line->amount, 2) }}</dd>
                    </div>
                @endforeach
                <div class="flex justify-between border-t border-white/10 pt-1 font-medium">
                    <dt>Subtotal</dt>
                    <dd class="font-mono">${{ number_format($result->totalAud(), 2) }} AUD</dd>
                </div>
            </dl>

            <p class="mt-5 text-xs tracking-widest text-white/40 uppercase">Paid in Bangladesh</p>
            <dl class="mt-2 space-y-1 text-sm">
                @foreach ($result->bdtLineItems as $line)
                    <div class="flex justify-between gap-4">
                        <dt>{{ $line->label }}</dt>
                        <dd class="font-mono">৳{{ number_format($line->amount, 0) }}</dd>
                    </div>
                @endforeach
                <div class="flex justify-between border-t border-white/10 pt-1 font-medium">
                    <dt>Subtotal</dt>
                    <dd class="font-mono">৳{{ number_format($result->totalBdt(), 0) }} BDT</dd>
                </div>
            </dl>

            @if ($result->combinedBdt() !== null)
                <div class="mt-5 border-t border-white/20 pt-4">
                    <p class="font-display text-2xl">৳{{ number_format($result->combinedBdt(), 0) }}</p>
                    <p class="text-xs text-white/50">All in, at the stored rate of {{ number_format($result->bdtPerAudRate, 2) }} BDT per AUD.</p>
                </div>
            @else
                <p class="mt-5 text-xs text-white/50">No exchange rate is stored, so the two sides are shown separately.</p>
            @endif
        @else
            <p class="mt-4 text-sm text-white/60">Pick a screen size to see the landed cost.</p>
        @endif
    </aside>
</div>
