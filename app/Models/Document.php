<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Document extends Model
{
    public const PROOF_OF_DELIVERY = 'proof_of_delivery';

    public const INVOICE = 'invoice';

    public const SHIPPING_LABEL = 'shipping_label';

    public const CUSTOMS_DECLARATION = 'customs_declaration';

    public const PARCEL_PHOTO = 'parcel_photo';

    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return MorphTo<Model, $this> */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /** The only documents a customer may be shown. */
    public function scopeCustomerVisible(Builder $query): void
    {
        $query->where('is_customer_visible', true);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_customer_visible' => 'boolean',
            'size_bytes' => 'integer',
        ];
    }
}
