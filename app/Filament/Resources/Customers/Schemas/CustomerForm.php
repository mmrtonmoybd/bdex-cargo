<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('password')
                    ->password()
                    ->default(null),
                TextInput::make('preferred_locale')
                    ->required()
                    ->default('en'),
                TextInput::make('preferred_currency')
                    ->required()
                    ->default('AUD'),
                Textarea::make('notification_channels')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_business')
                    ->required(),
                TextInput::make('business_name')
                    ->default(null),
                Textarea::make('default_address')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('email_verified_at'),
                DateTimePicker::make('phone_verified_at'),
            ]);
    }
}
