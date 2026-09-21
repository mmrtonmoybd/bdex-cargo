<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'id')
                    ->required(),
                TextInput::make('driver_class')
                    ->required(),
                TextInput::make('driver_name')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required(),
                Select::make('status')
                    ->options(PaymentStatus::class)
                    ->required(),
                TextInput::make('gateway_reference')
                    ->default(null),
                TextInput::make('redirect_url')
                    ->url()
                    ->default(null),
                Textarea::make('raw_payload')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('failure_reason')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('recorded_by')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
