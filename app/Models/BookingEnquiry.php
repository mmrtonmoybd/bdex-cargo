<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BookingEnquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A pickup request from the public booking wizard.
 *
 * @property string $reference
 * @property array<string, mixed>|null $quote_snapshot
 */
final class BookingEnquiry extends Model
{
    /** @use HasFactory<BookingEnquiryFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return BelongsTo<ShippingRoute, $this> */
    public function shippingRoute(): BelongsTo
    {
        return $this->belongsTo(ShippingRoute::class);
    }

    /** @return BelongsTo<ItemCategory, $this> */
    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    /** Reference the customer is given, and quotes back when they ask about it. */
    public static function nextReference(): string
    {
        do {
            $reference = 'BDX-'.random_int(1_000_000, 9_999_999);
        } while (self::query()->where('reference', $reference)->exists());

        return $reference;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'quote_snapshot' => 'array',
            'terms_accepted' => 'boolean',
            'box_count' => 'integer',
        ];
    }
}
