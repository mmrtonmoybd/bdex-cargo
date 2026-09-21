<?php

declare(strict_types=1);

namespace App\Filament\Resources\Carriers\Schemas;

use App\Services\Carrier\Adapters\AramexTrackingAdapter;
use App\Services\Carrier\Adapters\DhlTrackingAdapter;
use App\Services\Carrier\Adapters\FedExTrackingAdapter;
use App\Services\Carrier\Adapters\UpsTrackingAdapter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class CarrierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Carrier')
                    ->description('Internal only. Nothing here is ever shown to a customer.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->helperText('Used to namespace this carrier\'s credentials, e.g. fedex → carriers.fedex.access_token.')
                                ->required()
                                ->alphaDash()
                                ->maxLength(60)
                                ->unique(ignoreRecord: true),
                        ]),
                    ]),

                Section::make('Integration')
                    ->description('Adding a carrier nobody has integrated with needs one adapter class written against CarrierTrackingAdapter. Everything after that — credentials, activation, base URL — is editable here.')
                    ->schema([
                        Select::make('driver_class')
                            ->label('Tracking adapter')
                            ->options([
                                AramexTrackingAdapter::class => 'Aramex',
                                FedExTrackingAdapter::class => 'FedEx',
                                DhlTrackingAdapter::class => 'DHL',
                                UpsTrackingAdapter::class => 'UPS',
                            ])
                            ->helperText('The class that knows this carrier\'s API and translates its statuses into ours.')
                            ->required()
                            ->searchable(),
                        TextInput::make('api_base_url')
                            ->label('API base URL')
                            ->url()
                            ->maxLength(255),
                        Grid::make(2)->schema([
                            TextInput::make('poll_interval_hours')
                                ->label('Poll every (hours)')
                                ->numeric()
                                ->minValue(1)
                                ->default(12),
                            Toggle::make('is_active')
                                ->label('Active')
                                ->helperText('Inactive carriers are skipped by the sync entirely.')
                                ->default(true),
                        ]),
                        Text::make('Credentials for this carrier live under System → Configuration, encrypted at rest. Rotating one takes effect on the next poll — no deploy.')
                            ->color('gray'),
                    ]),
            ]);
    }
}
