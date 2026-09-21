<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\ConfigEntry;
use App\Services\Config\DynamicConfig;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * Runtime configuration, grouped into tabs.
 *
 * The form is built from whatever `config_entries` rows exist — there is no field
 * hardcoded per provider here. Adding a credential for a second SMS vendor is a
 * new row (via Configuration keys, or a seeder), and this page picks it up on the
 * next load with no PHP change.
 *
 * The honest boundary: a provider nobody has written a driver class for still
 * needs that one class before these credentials mean anything. Everything after
 * that first class — rotating keys, switching which provider is active, adding a
 * second account — happens here.
 */
class ManageConfiguration extends Page
{
    protected string $view = 'filament.pages.manage-configuration';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Configuration';

    /** Human labels for the groups we ship with; unknown groups fall back to the raw name. */
    private const GROUP_LABELS = [
        'pricing' => 'Pricing',
        'carriers' => 'Carriers',
        'notifications' => 'Notifications',
        'payments' => 'Payments',
        'fx' => 'Exchange rates',
        'misc' => 'Other',
    ];

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill(
            $this->entries()
                ->mapWithKeys(fn (ConfigEntry $entry): array => [
                    self::fieldName($entry->key) => $entry->plainValue(),
                ])
                ->all()
        );
    }

    public function form(Schema $schema): Schema
    {
        $groups = $this->entries()->groupBy('group');

        if ($groups->isEmpty()) {
            return $schema->statePath('data')->components([
                Section::make('Nothing configured yet')
                    ->description('Add keys under Configuration keys and they will appear here, grouped into tabs.'),
            ]);
        }

        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()
                    ->tabs(
                        $groups->map(fn (Collection $entries, string $group): Tabs\Tab => Tabs\Tab::make(
                            self::GROUP_LABELS[$group] ?? ucfirst($group)
                        )->schema(
                            $entries->map(fn (ConfigEntry $entry): TextInput => $this->field($entry))->all()
                        ))->values()->all()
                    )
                    ->columnSpanFull(),
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
        $state = $this->form->getState();

        foreach ($this->entries() as $entry) {
            $field = self::fieldName($entry->key);

            if (! array_key_exists($field, $state)) {
                continue;
            }

            $value = $state[$field];

            // A masked field left untouched submits null; that must not wipe a
            // credential nobody meant to change.
            if ($entry->is_secret && ($value === null || $value === '')) {
                continue;
            }

            DynamicConfig::set($entry->key, $value, $entry->is_secret);
        }

        Notification::make()
            ->title('Configuration saved')
            ->body('New values take effect on the next request — no deploy needed.')
            ->success()
            ->send();
    }

    private function field(ConfigEntry $entry): TextInput
    {
        return TextInput::make(self::fieldName($entry->key))
            ->label($entry->label)
            ->helperText($entry->description)
            ->hint($entry->key)
            ->password($entry->is_secret)
            ->revealable($entry->is_secret)
            ->autocomplete(false)
            ->placeholder($entry->is_secret ? 'Unchanged' : null);
    }

    /** @return Collection<int, ConfigEntry> */
    private function entries(): Collection
    {
        return ConfigEntry::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    /** Dots are not valid in a Livewire state path, so keys are flattened. */
    private static function fieldName(string $key): string
    {
        return str_replace('.', '__', $key);
    }
}
