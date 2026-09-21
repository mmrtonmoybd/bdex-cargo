<?php

declare(strict_types=1);

namespace App\Filament\Resources\SurchargeRules;

use App\Filament\Resources\SurchargeRules\Pages\CreateSurchargeRule;
use App\Filament\Resources\SurchargeRules\Pages\EditSurchargeRule;
use App\Filament\Resources\SurchargeRules\Pages\ListSurchargeRules;
use App\Filament\Resources\SurchargeRules\Schemas\SurchargeRuleForm;
use App\Filament\Resources\SurchargeRules\Tables\SurchargeRulesTable;
use App\Models\SurchargeRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SurchargeRuleResource extends Resource
{
    protected static ?string $model = SurchargeRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return SurchargeRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SurchargeRulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSurchargeRules::route('/'),
            'create' => CreateSurchargeRule::route('/create'),
            'edit' => EditSurchargeRule::route('/{record}/edit'),
        ];
    }
}
