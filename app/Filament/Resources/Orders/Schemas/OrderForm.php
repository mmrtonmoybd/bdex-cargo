<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\FulfilmentMethod;
use App\Enums\NormalizedStatus;
use App\Enums\PaymentStatus;
use App\Enums\ServiceLevel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->tabs([
                Tabs\Tab::make('Shipment')->schema([
                    Section::make()->schema([
                        Grid::make(3)->schema([
                            TextInput::make('bdex_tracking_number')
                                ->label('BD-EX number')
                                ->helperText('Issued automatically and never changes.')
                                ->disabled()
                                ->dehydrated(false),
                            Select::make('shipping_route_id')
                                ->label('Route')
                                ->relationship('shippingRoute', 'name')
                                ->required()
                                ->preload(),
                            Select::make('pricing_zone_id')
                                ->label('Origin zone')
                                ->relationship('pricingZone', 'name')
                                ->preload(),
                        ]),
                        Grid::make(3)->schema([
                            Select::make('status')
                                ->options(NormalizedStatus::class)
                                ->enum(NormalizedStatus::class)
                                ->helperText('Prefer the "Add status" action — it writes the timeline too.')
                                ->required(),
                            Select::make('service_level')
                                ->options(ServiceLevel::class)
                                ->enum(ServiceLevel::class)
                                ->required(),
                            Select::make('fulfilment_method')
                                ->label('How it reaches us')
                                ->options(FulfilmentMethod::class)
                                ->enum(FulfilmentMethod::class)
                                ->required(),
                        ]),
                    ]),
                ]),

                Tabs\Tab::make('Sender & receiver')->schema([
                    Section::make('Sender')->schema([
                        Grid::make(2)->schema([
                            TextInput::make('sender_name')->required(),
                            TextInput::make('sender_phone')->tel()->required(),
                        ]),
                        Textarea::make('sender_address')->rows(2)->required(),
                        Grid::make(2)->schema([
                            TextInput::make('sender_city'),
                            TextInput::make('sender_postcode'),
                        ]),
                    ]),
                    Section::make('Receiver')->schema([
                        Grid::make(2)->schema([
                            TextInput::make('receiver_name')->required(),
                            TextInput::make('receiver_phone')->tel()->required(),
                        ]),
                        Textarea::make('receiver_address')->rows(2)->required(),
                        Grid::make(2)->schema([
                            TextInput::make('receiver_city'),
                            TextInput::make('receiver_postcode'),
                        ]),
                    ]),
                ]),

                Tabs\Tab::make('Pickup & handling')->schema([
                    Grid::make(3)->schema([
                        DatePicker::make('pickup_preferred_date')->label('Preferred date'),
                        TextInput::make('pickup_window')->label('Window')->placeholder('Morning · 9am–1pm'),
                        Toggle::make('is_fragile')->label('Fragile'),
                        Toggle::make('is_cold_storage')->label('Cold storage'),
                    ]),
                    Textarea::make('special_instructions')->rows(3)->columnSpanFull(),
                ]),

                Tabs\Tab::make('Money')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('total_cost_aud')->label('Total (AUD)')->numeric()->prefix('$'),
                        TextInput::make('total_cost_bdt')->label('Total (BDT)')->numeric()->suffix('BDT'),
                        Select::make('payment_status')
                            ->options(PaymentStatus::class)
                            ->enum(PaymentStatus::class)
                            ->required(),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('declared_value')->numeric()->prefix('$'),
                        Toggle::make('has_product_cover_insurance')->label('Product Cover Insurance'),
                        TextInput::make('insurance_fee')->numeric()->prefix('$'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('paid_amount')->numeric()->prefix('$'),
                        TextInput::make('payment_reference'),
                    ]),
                ]),
            ])->columnSpanFull(),
        ]);
    }
}
