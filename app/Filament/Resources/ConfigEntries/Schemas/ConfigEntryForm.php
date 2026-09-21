<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConfigEntries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * Defines a configuration key.
 *
 * Day-to-day editing happens on the Configuration page, which groups these into
 * tabs; this resource exists for adding a key that does not exist yet.
 */
class ConfigEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Key')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('key')
                                ->helperText('Dot-namespaced, e.g. carriers.fedex.client_secret or sms.driver.')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            Select::make('group')
                                ->helperText('Decides which tab this appears under on the Configuration page.')
                                ->options([
                                    'pricing' => 'Pricing',
                                    'carriers' => 'Carriers',
                                    'notifications' => 'Notifications',
                                    'payments' => 'Payments',
                                    'fx' => 'Exchange rates',
                                    'misc' => 'Other',
                                ])
                                ->default('misc')
                                ->required()
                                ->searchable()
                                ->createOptionForm([
                                    TextInput::make('group')->required(),
                                ]),
                        ]),
                        TextInput::make('label')
                            ->helperText('Shown to staff instead of the raw key.')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Value')
                    ->schema([
                        Toggle::make('is_secret')
                            ->label('Treat as a secret')
                            ->helperText('Encrypts the value at rest and masks it in every form. Turn this on for tokens, keys and passwords.')
                            ->live(),
                        TextInput::make('value')
                            ->password(fn (Get $get): bool => (bool) $get('is_secret'))
                            ->revealable(fn (Get $get): bool => (bool) $get('is_secret'))
                            ->autocomplete(false)
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
