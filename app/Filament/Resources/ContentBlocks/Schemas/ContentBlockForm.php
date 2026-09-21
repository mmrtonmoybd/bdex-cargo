<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContentBlocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContentBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Section')
                    ->description('The public site renders whatever is here. A section with no active block simply does not appear.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('section_key')
                                ->label('Section key')
                                ->helperText('Matches the section on the public page, e.g. hero.')
                                ->required()
                                ->maxLength(255),
                            Select::make('locale')
                                ->options(['en' => 'English', 'bn' => 'বাংলা'])
                                ->default('en')
                                ->required(),
                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ]),
                    ]),

                Section::make('Copy')
                    ->schema([
                        TextInput::make('title')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->rows(5)
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('icon')
                                ->helperText('Heroicon name, e.g. heroicon-o-truck.')
                                ->maxLength(255),
                            Toggle::make('is_active')
                                ->label('Visible')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }
}
