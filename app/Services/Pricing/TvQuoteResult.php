<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use Illuminate\Support\Collection;

/**
 * Landed cost for a TV, kept in both currencies on purpose.
 *
 * The freight side is billed in AUD and the Bangladesh side (government duty,
 * local home delivery) in BDT. Collapsing them into one number would hide which
 * half an FX movement actually moves, so the two subtotals stay separate and the
 * combined figure is offered only when a stored rate exists.
 */
final readonly class TvQuoteResult
{
    /**
     * @param  Collection<int, QuoteLineItem>  $audLineItems
     * @param  Collection<int, QuoteLineItem>  $bdtLineItems
     */
    public function __construct(
        public Collection $audLineItems,
        public Collection $bdtLineItems,
        public ?float $bdtPerAudRate = null,
        public ?string $fxFetchedAt = null,
    ) {}

    public function totalAud(): float
    {
        return round($this->audLineItems->sum(fn (QuoteLineItem $line): float => $line->amount), 2);
    }

    public function totalBdt(): float
    {
        return round($this->bdtLineItems->sum(fn (QuoteLineItem $line): float => $line->amount), 2);
    }

    /** Whole landed cost expressed in BDT, or null when no rate is stored. */
    public function combinedBdt(): ?float
    {
        return $this->bdtPerAudRate === null
            ? null
            : round($this->totalBdt() + ($this->totalAud() * $this->bdtPerAudRate), 2);
    }

    /** Whole landed cost expressed in AUD, or null when no rate is stored. */
    public function combinedAud(): ?float
    {
        return $this->bdtPerAudRate === null || $this->bdtPerAudRate <= 0.0
            ? null
            : round($this->totalAud() + ($this->totalBdt() / $this->bdtPerAudRate), 2);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'aud_line_items' => $this->audLineItems->map(fn (QuoteLineItem $l): array => $l->toArray())->all(),
            'bdt_line_items' => $this->bdtLineItems->map(fn (QuoteLineItem $l): array => $l->toArray())->all(),
            'total_aud' => $this->totalAud(),
            'total_bdt' => $this->totalBdt(),
            'bdt_per_aud_rate' => $this->bdtPerAudRate,
            'fx_fetched_at' => $this->fxFetchedAt,
            'combined_aud' => $this->combinedAud(),
            'combined_bdt' => $this->combinedBdt(),
        ];
    }
}
