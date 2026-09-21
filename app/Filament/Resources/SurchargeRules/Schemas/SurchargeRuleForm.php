<?php

declare(strict_types=1);

namespace App\Filament\Resources\SurchargeRules\Schemas;

use App\Enums\CalculationType;
use App\Enums\ConditionField;
use App\Enums\ConditionOperator;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * The fee builder.
 *
 * Every conditional charge the business makes is assembled here rather than
 * written in PHP, so a new fee is data entry and an obsolete one is a toggle.
 */
class SurchargeRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rule')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('code')
                                ->helperText('Machine name. Appears on the customer\'s itemised quote as the line\'s source.')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            TextInput::make('label')
                                ->helperText('What the customer sees on the quote.')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Where it applies')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('shipping_route_id')
                                ->label('Route')
                                ->relationship('shippingRoute', 'name')
                                ->placeholder('Every route')
                                ->helperText('Leave empty to charge this on all routes.')
                                ->preload()
                                ->searchable(),
                            Select::make('item_category_id')
                                ->label('Item category')
                                ->relationship('itemCategory', 'name')
                                ->placeholder('Whole shipment')
                                ->helperText('Pick a category to charge per matching line instead of once per shipment.')
                                ->preload()
                                ->searchable(),
                        ]),
                    ]),

                Section::make('How much')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('calculation_type')
                                ->label('Calculation')
                                ->options(CalculationType::class)
                                ->enum(CalculationType::class)
                                ->required()
                                ->live()
                                ->default(CalculationType::Flat),
                            TextInput::make('amount')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->prefix(fn (Get $get): ?string => CalculationType::tryFrom((string) $get('calculation_type')) === CalculationType::Percentage ? null : '$')
                                ->suffix(fn (Get $get): ?string => match (CalculationType::tryFrom((string) $get('calculation_type'))) {
                                    CalculationType::Percentage => '%',
                                    CalculationType::PerKg => 'per kg',
                                    CalculationType::PerItem => 'per item',
                                    default => null,
                                }),
                        ]),
                        Toggle::make('is_placeholder_amount')
                            ->label('Amount is a placeholder')
                            ->helperText('Flags this rule on the dashboard until a confirmed figure replaces it. The rule still charges while flagged.')
                            ->columnSpanFull(),
                    ]),

                Section::make('When it applies')
                    ->description('Leave the field empty to charge this on every shipment the route and category allow.')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('condition_field')
                                ->label('Only when')
                                ->options(ConditionField::class)
                                ->enum(ConditionField::class)
                                ->placeholder('Always')
                                ->live(),
                            Select::make('condition_operator')
                                ->label('Comparison')
                                ->options(fn (Get $get): array => self::operatorsFor($get('condition_field')))
                                ->enum(ConditionOperator::class)
                                ->live()
                                ->required(fn (Get $get): bool => filled($get('condition_field')))
                                ->visible(fn (Get $get): bool => filled($get('condition_field'))),
                            TextInput::make('condition_value')
                                ->label('Value')
                                ->numeric()
                                ->required(fn (Get $get): bool => ConditionOperator::tryFrom((string) $get('condition_operator'))?->requiresValue() ?? false)
                                ->visible(fn (Get $get): bool => ConditionOperator::tryFrom((string) $get('condition_operator'))?->requiresValue() ?? false),
                        ]),
                    ]),

                Section::make('Visibility')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')
                                ->helperText('Order the rule is evaluated and listed in.')
                                ->numeric()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }

    /**
     * A boolean fact only supports "is true"; a numeric one supports comparisons.
     *
     * @return array<string, string>
     */
    private static function operatorsFor(mixed $field): array
    {
        $field = ConditionField::tryFrom((string) $field);

        $operators = $field?->isBoolean() === true
            ? [ConditionOperator::IsTrue]
            : array_filter(ConditionOperator::cases(), fn (ConditionOperator $o): bool => $o !== ConditionOperator::IsTrue);

        return collect($operators)
            ->mapWithKeys(fn (ConditionOperator $o): array => [$o->value => $o->getLabel()])
            ->all();
    }
}
