<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SellerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Seller extends Model
{
    /** @use HasFactory<SellerFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<SellerListing, $this> */
    public function listings(): HasMany
    {
        return $this->hasMany(SellerListing::class);
    }

    /** @return HasMany<SellerReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(SellerReview::class);
    }

    /** Recomputes the cached rating from published reviews only. */
    public function refreshRating(): void
    {
        $published = $this->reviews()->where('is_published', true);

        $this->forceFill([
            'rating_average' => round((float) $published->avg('rating'), 2),
            'rating_count' => $published->count(),
        ])->save();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['rating_count' => 'integer'];
    }
}
