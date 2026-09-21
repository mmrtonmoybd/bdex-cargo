<?php

declare(strict_types=1);

namespace App\Filament\Resources\PricingAgreements;

use App\Filament\Resources\PricingAgreements\Pages\CreatePricingAgreement;
use App\Filament\Resources\PricingAgreements\Pages\EditPricingAgreement;
use App\Filament\Resources\PricingAgreements\Pages\ListPricingAgreements;
use App\Filament\Resources\PricingAgreements\Schemas\PricingAgreementForm;
use App\Filament\Resources\PricingAgreements\Tables\PricingAgreementsTable;
use App\Models\PricingAgreement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PricingAgreementResource extends Resource
{
    protected static ?string $model = PricingAgreement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Volume & contract rates';

    public static function form(Schema $schema): Schema
    {
        return PricingAgreementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PricingAgreementsTable::configure($table);
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
            'index' => ListPricingAgreements::route('/'),
            'create' => CreatePricingAgreement::route('/create'),
            'edit' => EditPricingAgreement::route('/{record}/edit'),
        ];
    }
}
