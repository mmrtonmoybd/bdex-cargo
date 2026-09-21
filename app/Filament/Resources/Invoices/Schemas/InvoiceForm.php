<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'id')
                    ->required(),
                TextInput::make('invoice_number')
                    ->required(),
                TextInput::make('pdf_path')
                    ->default(null),
                Textarea::make('line_items')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('currency')
                    ->required()
                    ->default('AUD'),
                TextInput::make('tax_rate_percent')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_label')
                    ->default(null),
                DateTimePicker::make('issued_at'),
            ]);
    }
}
