<?php

declare(strict_types=1);

namespace App\Filament\Resources\ItemCategories\RelationManagers;

use App\Models\ItemCategory;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-zone rates for one category.
 *
 * Only shown for categories priced per kg by zone — a flat-fee category has no
 * zone rates to set, and offering the tab anyway invites confusing data.
 */
class ZoneRatesRelationManager extends RelationManager
{
    protected static string $relationship = 'zoneRates';

    protected static ?string $title = 'Zone rates';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof ItemCategory
            && $ownerRecord->pricing_model->usesZoneRates();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('pricing_zone_id')
                ->label('Zone')
                ->options(fn (): array => $this->getOwnerRecord()
                    ->shippingRoute
                    ->pricingZones()
                    ->orderBy('sort_order')
                    ->pluck('name', 'id')
                    ->all())
                ->required()
                ->searchable(),
            TextInput::make('rate_per_kg')
                ->label('Rate per kg')
                ->numeric()
                ->prefix('$')
                ->minValue(0)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('rate_per_kg')
            ->columns([
                TextColumn::make('pricingZone.name')
                    ->label('Zone')
                    ->sortable(),
                TextColumn::make('rate_per_kg')
                    ->label('Rate per kg')
                    ->money('AUD'),
                TextColumn::make('updated_at')
                    ->label('Last changed')
                    ->since(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
