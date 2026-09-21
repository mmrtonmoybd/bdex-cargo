<?php

declare(strict_types=1);

namespace App\Filament\Portal\Pages;

use App\Models\Customer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Which channels a customer wants to hear from us on.
 *
 * Email cannot be turned off: a shipment with no reachable channel is worse for
 * the customer than one more email, and booking confirmations and delivery proof
 * have to land somewhere.
 */
class NotificationPreferences extends Page
{
    protected string $view = 'filament.portal.preferences';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|UnitEnum|null $navigationGroup = 'My shipping';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Notifications';

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $customer = $this->customer();

        $this->form->fill([
            'notification_channels' => $customer->channels(),
            'preferred_locale' => $customer->preferred_locale,
            'preferred_currency' => $customer->preferred_currency,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('How we reach you')
                    ->description('We send a message at every milestone — booked, picked up, in transit, customs, delivered.')
                    ->schema([
                        CheckboxList::make('notification_channels')
                            ->label('Channels')
                            ->options([
                                'mail' => 'Email',
                                'sms' => 'SMS',
                                'whatsapp' => 'WhatsApp',
                                'database' => 'In this portal',
                            ])
                            ->descriptions([
                                'mail' => 'Always on — this is where confirmations and invoices go.',
                                'sms' => 'Sent to the mobile number on your account.',
                                'whatsapp' => 'Sent to the same number, if it has WhatsApp.',
                                'database' => 'A bell notification here, with no message sent out.',
                            ])
                            ->columns(2)
                            ->required(),
                    ]),

                Section::make('Preferences')
                    ->schema([
                        Select::make('preferred_locale')
                            ->label('Language')
                            ->options(['en' => 'English', 'bn' => 'বাংলা'])
                            ->required(),
                        Select::make('preferred_currency')
                            ->label('Show prices in')
                            ->options(['AUD' => 'Australian dollars', 'BDT' => 'Bangladeshi taka'])
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')->label('Save')->submit('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $channels = collect($state['notification_channels'] ?? [])
            // Email stays on whatever the customer unticks.
            ->push('mail')
            ->unique()
            ->values()
            ->all();

        $this->customer()->forceFill([
            'notification_channels' => $channels,
            'preferred_locale' => $state['preferred_locale'],
            'preferred_currency' => $state['preferred_currency'],
        ])->save();

        Notification::make()->title('Preferences saved')->success()->send();
    }

    private function customer(): Customer
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        return $customer;
    }
}
