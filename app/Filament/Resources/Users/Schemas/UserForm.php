<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->helperText('Used to sign in, and where password resets are sent.')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
                ]),

            Section::make('Password')
                ->description('On an existing account, leave this empty to keep the current password.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->minLength(8)
                            // Required only when creating: an edit that leaves it blank
                            // must not wipe a working password.
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->same('password_confirmation'),
                        TextInput::make('password_confirmation')
                            ->label('Confirm password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            // Never stored; it exists only to catch a typo.
                            ->dehydrated(false),
                    ]),
                ]),

            Section::make('Access')
                ->description('A staff account with no role cannot sign in at all. What each role allows is set on the Roles & permissions screen.')
                ->schema([
                    Select::make('roles')
                        ->label('Roles')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->required()
                        ->options(fn (): array => Role::query()
                            ->where('guard_name', 'web')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->columnSpanFull(),
                    Text::make('Seeing which carrier actually carried a shipment is itself a permission. Grant it only to staff who need it.')
                        ->color('gray'),
                    DateTimePicker::make('email_verified_at')
                        ->label('Email verified')
                        ->helperText('Set when the address has been confirmed. Optional for staff you create yourself.'),
                ]),
        ]);
    }
}
