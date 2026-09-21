<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Config\DynamicConfig;
use Database\Factories\CarrierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A real carrier. Internal only — nothing a customer can reach may read this.
 *
 * @property string $code
 * @property class-string $driver_class
 */
final class Carrier extends Model
{
    /** @use HasFactory<CarrierFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return HasMany<CarrierConsignment, $this> */
    public function consignments(): HasMany
    {
        return $this->hasMany(CarrierConsignment::class);
    }

    /**
     * One of this carrier's credentials.
     *
     * Namespaced per carrier code, so a second account with the same carrier is a
     * new row here and a new set of config entries — not new PHP.
     */
    public function credential(string $name, mixed $default = null): mixed
    {
        return DynamicConfig::get("carriers.{$this->code}.{$name}", $default);
    }

    /** The config key an admin edits for one of this carrier's credentials. */
    public function credentialKey(string $name): string
    {
        return "carriers.{$this->code}.{$name}";
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'poll_interval_hours' => 'integer',
        ];
    }
}
