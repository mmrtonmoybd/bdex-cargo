<?php

declare(strict_types=1);

namespace App\Filament\Portal\Resources\Listings\Tables;

use App\Models\SellerListing;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No listings yet')
            ->emptyStateDescription('Create one to resell our shipping under your own name and price.')
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('shippingRoute.name')->label('Route'),
                TextColumn::make('itemCategory.name')->label('Category')->placeholder('Any'),
                TextColumn::make('margin')
                    ->label('Your margin')
                    ->state(function (SellerListing $record): string {
                        $parts = [];

                        if ($record->markup_percent !== null) {
                            $parts[] = rtrim(rtrim((string) $record->markup_percent, '0'), '.').'%';
                        }

                        if ($record->flat_markup_amount !== null) {
                            $parts[] = '$'.number_format((float) $record->flat_markup_amount, 2);
                        }

                        return $parts === [] ? 'Account default' : implode(' + ', $parts);
                    }),
                IconColumn::make('is_active')->label('Listed')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
