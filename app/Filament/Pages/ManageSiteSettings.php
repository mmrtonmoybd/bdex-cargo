<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\SiteSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Display configuration for the public site.
 *
 * Nothing here is secret, which is why it is a different page and a different
 * table from Configuration — the public site reads this row on every request and
 * must never touch anything encrypted.
 */
class ManageSiteSettings extends Page
{
    protected string $view = 'filament.pages.manage-site-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Site settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->record()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()->tabs([
                    Tabs\Tab::make('Branding')
                        ->icon(Heroicon::OutlinedSwatch)
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('business_name')->required()->maxLength(255),
                                TextInput::make('tagline')->maxLength(255),
                            ]),
                            Grid::make(2)->schema([
                                FileUpload::make('logo_path')
                                    ->label('Logo')
                                    ->image()
                                    ->directory('branding')
                                    ->visibility('public'),
                                FileUpload::make('favicon_path')
                                    ->label('Favicon')
                                    ->image()
                                    ->directory('branding')
                                    ->visibility('public'),
                            ]),
                            Section::make('Brand colours')
                                ->description('These reach the public site as CSS custom properties. A change is live on the next page load — no deploy, no cache clear.')
                                ->schema([
                                    Grid::make(3)->schema([
                                        ColorPicker::make('color_void')->label('Deep background')->required(),
                                        ColorPicker::make('color_signal')->label('Signal green')->required(),
                                        ColorPicker::make('color_red')->label('Brand red')->required(),
                                        ColorPicker::make('color_blue')->label('Brand blue')->required(),
                                        ColorPicker::make('color_bg')->label('Page background')->required(),
                                        ColorPicker::make('color_ink')->label('Body text')->required(),
                                    ]),
                                ]),
                            Section::make('Typefaces')
                                ->description('Full CSS font stacks, including fallbacks.')
                                ->schema([
                                    TextInput::make('font_display')->label('Display')->required(),
                                    TextInput::make('font_body')->label('Body')->required(),
                                    TextInput::make('font_mono')->label('Monospace')->required(),
                                ]),
                        ]),

                    Tabs\Tab::make('Contact')
                        ->icon(Heroicon::OutlinedPhone)
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('contact_phone_au')->label('Australia phone')->tel(),
                                TextInput::make('contact_phone_bd')->label('Bangladesh phone')->tel(),
                                TextInput::make('contact_whatsapp')->label('WhatsApp')->tel(),
                                TextInput::make('contact_email')->label('Email')->email(),
                            ]),
                            Grid::make(2)->schema([
                                Textarea::make('address_au')->label('Australia address')->rows(3),
                                Textarea::make('address_bd')->label('Bangladesh address')->rows(3),
                            ]),
                        ]),

                    Tabs\Tab::make('Prohibited items')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->schema([
                            Repeater::make('banned_items')
                                ->label('Flagged keywords')
                                ->helperText('Matched against packing-list descriptions. A match flags the order for staff review rather than blocking the booking.')
                                ->simple(
                                    TextInput::make('term')->required()->maxLength(255),
                                )
                                ->reorderable()
                                ->addActionLabel('Add keyword')
                                ->columnSpanFull(),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save changes')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $record = $this->record();
        $record->fill($this->form->getState());
        // Saving clears the public cache through InvalidatesSiteCache, so the very
        // next visitor sees the new colours without anyone clearing anything.
        $record->save();

        Notification::make()
            ->title('Site settings saved')
            ->body('The public site is already serving these values.')
            ->success()
            ->send();
    }

    private function record(): SiteSetting
    {
        return SiteSetting::query()->firstOrCreate([], app(SiteSettings::class)->model()->attributesToArray());
    }
}
