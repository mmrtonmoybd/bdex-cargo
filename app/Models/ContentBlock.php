<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\InvalidatesSiteCache;
use Database\Factories\ContentBlockFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $section_key
 * @property string $locale
 * @property string|null $title
 * @property string|null $body
 * @property string|null $icon
 * @property int $sort_order
 * @property bool $is_active
 */
final class ContentBlock extends Model
{
    /** @use HasFactory<ContentBlockFactory> */
    use HasFactory;

    use InvalidatesSiteCache;

    protected $fillable = [
        'section_key',
        'locale',
        'title',
        'body',
        'icon',
        'sort_order',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
