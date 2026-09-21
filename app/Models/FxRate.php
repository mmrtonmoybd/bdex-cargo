<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FxRateSourceType;
use Database\Factories\FxRateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $from_currency
 * @property string $to_currency
 * @property string $rate
 * @property FxRateSourceType $source
 * @property Carbon $fetched_at
 */
final class FxRate extends Model
{
    /** @use HasFactory<FxRateFactory> */
    use HasFactory;

    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'source',
        'fetched_at',
    ];

    /** @param Builder<self> $query */
    public function scopeForPair(Builder $query, string $from, string $to): void
    {
        $query->where('from_currency', $from)->where('to_currency', $to);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'source' => FxRateSourceType::class,
            'fetched_at' => 'datetime',
        ];
    }
}
