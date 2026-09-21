<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use Illuminate\Support\Collection;

/**
 * What every price the customer sees comes out of.
 *
 * No controller or Livewire component adds up a total itself; they render this.
 */
final readonly class QuoteResult
{
    /** @param Collection<int, QuoteLineItem> $lineItems */
    public function __construct(
        public Collection $lineItems,
        public string $baseCurrency,
        public string $quoteCurrency,
        public float $taxRatePercent = 0.0,
        public ?float $fxRateUsed = null,
        public ?string $fxFetchedAt = null,
    ) {}

    public function subtotal(): float
    {
        return round($this->lineItems->sum(fn (QuoteLineItem $item): float => $item->amount), 2);
    }

    public function tax(): float
    {
        return round($this->subtotal() * ($this->taxRatePercent / 100), 2);
    }

    public function total(): float
    {
        return round($this->subtotal() + $this->tax(), 2);
    }

    /** Total in the route's quote currency, or null when no FX rate is available. */
    public function convertedTotal(): ?float
    {
        return $this->fxRateUsed === null ? null : round($this->total() * $this->fxRateUsed, 2);
    }

    /** @return array<string, mixed> The shape stored on orders.cost_breakdown. */
    public function toArray(): array
    {
        return [
            'line_items' => $this->lineItems->map(fn (QuoteLineItem $item): array => $item->toArray())->all(),
            'subtotal' => $this->subtotal(),
            'tax_rate_percent' => $this->taxRatePercent,
            'tax' => $this->tax(),
            'total' => $this->total(),
            'base_currency' => $this->baseCurrency,
            'quote_currency' => $this->quoteCurrency,
            'fx_rate_used' => $this->fxRateUsed,
            'fx_fetched_at' => $this->fxFetchedAt,
            'converted_total' => $this->convertedTotal(),
        ];
    }
}
