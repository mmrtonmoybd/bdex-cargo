<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ConfigEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * @property string $key
 * @property string|null $value
 * @property bool $is_secret
 * @property string $group
 * @property string $label
 * @property string|null $description
 * @property int $sort_order
 */
final class ConfigEntry extends Model
{
    /** @use HasFactory<ConfigEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'is_secret',
        'group',
        'label',
        'description',
        'sort_order',
    ];

    /**
     * The stored value in the clear.
     *
     * Encryption is an implementation detail of the row, so every reader — the
     * DynamicConfig cache, the Filament form — goes through here and none of them
     * has to remember whether this particular key is a secret.
     */
    public function plainValue(): ?string
    {
        if ($this->value === null || $this->value === '') {
            return $this->value;
        }

        return $this->is_secret ? Crypt::decryptString($this->value) : $this->value;
    }

    /** Stores a value, encrypting it when the row is marked secret. */
    public function fillPlainValue(?string $value): void
    {
        $this->value = $value !== null && $value !== '' && $this->is_secret
            ? Crypt::encryptString($value)
            : $value;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_secret' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
