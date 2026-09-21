<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BoxesRelationManager extends RelationManager
{
    protected static string $relationship = 'boxes';

    protected static ?string $title = 'Boxes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('position')->label('Box number')->numeric()->default(1)->required(),
                TextInput::make('weight_kg')->label('Weight')->numeric()->suffix('kg')->required(),
            ]),
            Grid::make(3)->schema([
                TextInput::make('length_cm')->label('Length')->numeric()->suffix('cm'),
                TextInput::make('width_cm')->label('Width')->numeric()->suffix('cm'),
                TextInput::make('height_cm')->label('Height')->numeric()->suffix('cm'),
            ]),
            TextInput::make('barcode')
                ->helperText('Generated when the label is printed; enter one only to match an existing label.')
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('position')
            ->columns([
                TextColumn::make('position')->label('#')->sortable(),
                TextColumn::make('weight_kg')->label('Weight')->suffix(' kg'),
                TextColumn::make('dimensions')
                    ->label('Dimensions')
                    ->state(fn ($record): string => $record->length_cm === null
                        ? '—'
                        : "{$record->length_cm} × {$record->width_cm} × {$record->height_cm} cm"),
                TextColumn::make('barcode')->fontFamily('mono')->placeholder('Not printed'),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
