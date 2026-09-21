<?php

declare(strict_types=1);

namespace App\Filament\Portal\Resources\Listings\Schemas;

use App\Filament\Portal\Resources\Listings\ListingResource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('seller_id')->default(fn (): ?int => ListingResource::seller()?->id),

            Section::make('What you are offering')
                ->schema([
                    TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    Textarea::make('description')->rows(3)->columnSpanFull(),
                    Grid::make(2)->schema([
                        Select::make('shipping_route_id')
                            ->label('Route')
                            ->relationship('shippingRoute', 'name')
                            ->required()
                            ->preload(),
                        Select::make('item_category_id')
                            ->label('Item category')
                            ->relationship('itemCategory', 'name')
                            ->placeholder('Any category')
                            ->searchable()
                            ->preload(),
                    ]),
                ]),

            Section::make('Your margin')
                ->description('The base price always comes from our rate card, so when our rates change your listing follows. You only set what you add on top.')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('markup_percent')
                            ->label('Markup')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Leave empty to use your account default.'),
                        TextInput::make('flat_markup_amount')
                            ->label('Fixed amount on top')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                        Toggle::make('is_active')->label('Listed')->default(true),
                    ]),
                ]),
        ]);
    }
}
