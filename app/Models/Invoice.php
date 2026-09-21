<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property array<string, mixed>|null $line_items
 */
final class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Sequential per year, so a gap is visible rather than silent. */
    public static function nextNumber(): string
    {
        $prefix = 'INV-'.now()->format('Y');

        $latest = self::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = $latest === null ? 1 : ((int) substr($latest, -5)) + 1;

        return $prefix.'-'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'line_items' => 'array',
            'issued_at' => 'datetime',
        ];
    }
}
