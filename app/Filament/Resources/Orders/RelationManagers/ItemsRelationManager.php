<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\OrderItem;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The packing list. Customs reads it, and the prohibited-items check flags it.
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Packing list';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('description')->required()->columnSpanFull(),
            Grid::make(4)->schema([
                Select::make('item_category_id')
                    ->label('Category')
                    ->relationship('itemCategory', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('quantity')->numeric()->default(1)->required(),
                TextInput::make('weight_kg')->label('Weight')->numeric()->suffix('kg'),
                TextInput::make('declared_value')->numeric()->prefix('$'),
            ]),
            Grid::make(2)->schema([
                TextInput::make('unit')->default('pcs'),
                TextInput::make('liquid_volume_ml')
                    ->label('Liquid volume')
                    ->helperText('Required by customs for anything pourable.')
                    ->numeric()
                    ->suffix('ml'),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('description')->searchable()->wrap(),
                TextColumn::make('itemCategory.name')->label('Category')->placeholder('—'),
                TextColumn::make('quantity'),
                TextColumn::make('weight_kg')->label('Weight')->suffix(' kg'),
                TextColumn::make('declared_value')->money('AUD'),
                IconColumn::make('flagged_prohibited')
                    ->label('Flagged')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check')
                    ->falseColor('gray')
                    ->tooltip(fn (OrderItem $record): ?string => $record->flagged_term === null
                        ? null
                        : "Matched the banned-items list on '{$record->flagged_term}'"),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
