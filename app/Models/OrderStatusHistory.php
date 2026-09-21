<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NormalizedStatus;
use App\Enums\StatusSource;
use Database\Factories\OrderStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property NormalizedStatus $status
 * @property StatusSource $source
 */
final class OrderStatusHistory extends Model
{
    /** @use HasFactory<OrderStatusHistoryFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => NormalizedStatus::class,
            'source' => StatusSource::class,
            'occurred_at' => 'datetime',
        ];
    }
}
