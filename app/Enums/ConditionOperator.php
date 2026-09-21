<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Comparison applied between a surcharge rule's condition field and its value.
 *
 * `IsTrue` takes no `condition_value` — it fires on any truthy context value.
 */
enum ConditionOperator: string implements HasLabel
{
    case GreaterThan = 'gt';
    case GreaterThanOrEqual = 'gte';
    case LessThan = 'lt';
    case LessThanOrEqual = 'lte';
    case Equals = 'eq';
    case IsTrue = 'is_true';

    public function getLabel(): string
    {
        return match ($this) {
            self::GreaterThan => 'is greater than',
            self::GreaterThanOrEqual => 'is greater than or equal to',
            self::LessThan => 'is less than',
            self::LessThanOrEqual => 'is less than or equal to',
            self::Equals => 'equals',
            self::IsTrue => 'is true',
        };
    }

    /** Whether this operator needs a `condition_value` to compare against. */
    public function requiresValue(): bool
    {
        return $this !== self::IsTrue;
    }
}
