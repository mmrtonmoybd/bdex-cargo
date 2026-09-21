<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SellerReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SellerReview extends Model
{
    /** @use HasFactory<SellerReviewFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        // The cached average is only ever a cache: it is recomputed from the rows
        // whenever one changes, so it cannot drift.
        self::saved(fn (self $review) => $review->seller->refreshRating());
        self::deleted(fn (self $review) => $review->seller->refreshRating());
    }

    /** @return BelongsTo<Seller, $this> */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_published' => 'boolean',
        ];
    }
}
