<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
    case Refunded = 'refunded';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return (string) __('panel.payment.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Failed => 'danger',
            self::PartiallyPaid, self::Pending => 'warning',
            self::Refunded => 'gray',
        };
    }
}
