<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Enums\CalculationType;
use App\Enums\ConditionOperator;
use App\Models\SurchargeRule;

/**
 * Turns a surcharge_rules row into money, or into nothing.
 *
 * Every conditional fee the business charges — the airport fee, the high-value
 * electronics loading, the non-garment handling charge, the AusPost under-15kg
 * fee — is a row evaluated here. None of them is an `if` in PHP.
 */
final class SurchargeEvaluator
{
    /**
     * @param  float  $subtotal  Running base total, used by percentage rules.
     * @param  float  $weightKg  Chargeable weight, used by per-kg rules.
     * @param  int  $itemCount  Qualifying item count, used by per-item rules.
     */
    public function evaluate(
        SurchargeRule $rule,
        QuoteContext $context,
        float $subtotal = 0.0,
        float $weightKg = 0.0,
        int $itemCount = 1,
    ): ?QuoteLineItem {
        if (! $this->applies($rule, $context)) {
            return null;
        }

        $amount = $this->amount($rule, $subtotal, $weightKg, $itemCount);

        if ($amount === 0.0) {
            return null;
        }

        return new QuoteLineItem(
            label: $rule->label,
            amount: round($amount, 2),
            sourceType: 'surcharge_rule',
            sourceCode: $rule->code,
            detail: $rule->is_placeholder_amount
                ? 'Placeholder amount — pending confirmation'
                : $rule->description,
        );
    }

    public function applies(SurchargeRule $rule, QuoteContext $context): bool
    {
        if ($rule->isUnconditional()) {
            return true;
        }

        $field = $rule->condition_field;
        $operator = $rule->condition_operator;

        if ($field === null || $operator === null) {
            return true;
        }

        $actual = $context->value($field);

        if ($operator === ConditionOperator::IsTrue) {
            return (bool) $actual;
        }

        if ($rule->condition_value === null) {
            return false;
        }

        $expected = (float) $rule->condition_value;
        $actual = (float) $actual;

        return match ($operator) {
            ConditionOperator::GreaterThan => $actual > $expected,
            ConditionOperator::GreaterThanOrEqual => $actual >= $expected,
            ConditionOperator::LessThan => $actual < $expected,
            ConditionOperator::LessThanOrEqual => $actual <= $expected,
            ConditionOperator::Equals => abs($actual - $expected) < 0.00001,
            ConditionOperator::IsTrue => (bool) $actual,
        };
    }

    private function amount(SurchargeRule $rule, float $subtotal, float $weightKg, int $itemCount): float
    {
        $amount = (float) $rule->amount;

        return match ($rule->calculation_type) {
            CalculationType::Flat => $amount,
            CalculationType::Percentage => $subtotal * ($amount / 100),
            CalculationType::PerKg => $amount * $weightKg,
            CalculationType::PerItem => $amount * $itemCount,
        };
    }
}
