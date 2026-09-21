<?php

declare(strict_types=1);

namespace App\Filament\Resources\CarrierConsignments\RelationManagers;

use App\Models\Order;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * The orders consolidated onto this carrier booking.
 *
 * Attaching a second order here is what makes two customers share one carrier
 * number — and from then on they move together, at the same moment, with the same
 * wording, without either of them learning why.
 */
class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Orders on this consignment';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('bdex_tracking_number')
            ->columns([
                TextColumn::make('bdex_tracking_number')
                    ->label('BD-EX number')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('receiver_name')
                    ->label('Receiver')
                    ->searchable(),
                TextColumn::make('receiver_city')
                    ->label('To')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('total_cost_aud')
                    ->label('Value')
                    ->money('AUD'),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Attach an order')
                    ->recordSelectSearchColumns(['bdex_tracking_number', 'receiver_name', 'sender_name'])
                    ->recordSelectOptionsQuery(fn ($query) => $query->awaitingConsolidation()),
            ])
            ->recordActions([
                DissociateAction::make()
                    ->label('Remove')
                    ->modalDescription('The order keeps its history and its BD-EX number; it simply stops following this consignment.'),
            ])
            ->toolbarActions([
                DissociateBulkAction::make(),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('ViewCarrierDetails') ?? false;
    }
}
