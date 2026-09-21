<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ServiceAreaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $country
 * @property string $city
 * @property array<int, string>|null $postcodes
 */
final class ServiceArea extends Model
{
    /** @use HasFactory<ServiceAreaFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Whether a postcode falls inside this area.
     *
     * An area with no postcodes listed covers the whole city; otherwise a listed
     * value matches either exactly or as a prefix, which is how both Australian
     * four-digit codes and Bangladeshi ranges are expressed.
     */
    public function covers(?string $postcode): bool
    {
        $postcodes = $this->postcodes ?? [];

        if ($postcodes === []) {
            return true;
        }

        if ($postcode === null || $postcode === '') {
            return false;
        }

        $postcode = strtoupper(preg_replace('/\s+/', '', $postcode) ?? '');

        foreach ($postcodes as $candidate) {
            $candidate = strtoupper(preg_replace('/\s+/', '', (string) $candidate) ?? '');

            if ($candidate !== '' && str_starts_with($postcode, $candidate)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'postcodes' => 'array',
            'supports_pickup' => 'boolean',
            'supports_delivery' => 'boolean',
            'is_active' => 'boolean',
            'standard_days' => 'integer',
            'express_days' => 'integer',
        ];
    }
}
