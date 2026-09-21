<?php

declare(strict_types=1);

use App\Filament\Pages\ManageConfiguration;
use App\Filament\Pages\ManageSiteSettings;
use App\Models\ConfigEntry;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Config\DynamicConfig;
use App\Support\SiteSettings;
use Database\Seeders\ConfigEntrySeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $role = Role::query()->create(['name' => 'role-'.Str::random(6), 'guard_name' => 'web']);

    foreach (['View:ManageConfiguration', 'View:ManageSiteSettings'] as $permission) {
        $role->givePermissionTo(Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
    }

    $this->actingAs(User::factory()->create()->assignRole($role));
});

it('builds its tabs from whatever configuration rows exist', function (): void {
    $this->seed(ConfigEntrySeeder::class);

    ConfigEntry::query()->create([
        'key' => 'sms.acme.api_key',
        'group' => 'notifications',
        'label' => 'Acme SMS API key',
        'is_secret' => true,
    ]);

    livewire(ManageConfiguration::class)
        ->assertSuccessful()
        // A provider nobody wrote a field for still gets one, because the page is
        // built from the rows rather than from a hardcoded list.
        ->assertSee('Acme SMS API key')
        ->assertSee('TV freight rate (AUD per kg)');
});

it('saves a value that the engine then reads', function (): void {
    $this->seed(ConfigEntrySeeder::class);

    livewire(ManageConfiguration::class)
        ->set('data.pricing__tv__per_kg_rate_aud', '21')
        ->call('save')
        ->assertHasNoErrors();

    expect(DynamicConfig::float('pricing.tv.per_kg_rate_aud'))->toBe(21.0);
});

it('leaves a secret untouched when the masked field is submitted empty', function (): void {
    DynamicConfig::set('carriers.fedex.client_secret', 'original-secret', isSecret: true);

    ConfigEntry::query()->where('key', 'carriers.fedex.client_secret')->update([
        'group' => 'carriers',
        'label' => 'FedEx client secret',
    ]);

    livewire(ManageConfiguration::class)
        ->set('data.carriers__fedex__client_secret', '')
        ->call('save');

    expect(DynamicConfig::get('carriers.fedex.client_secret'))->toBe('original-secret');
});

it('saves site settings and clears the public cache', function (): void {
    $this->seed(SiteSettingSeeder::class);

    livewire(ManageSiteSettings::class)
        ->set('data.color_signal', '#123456')
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteSetting::query()->first()->color_signal)->toBe('#123456')
        ->and(app(SiteSettings::class)->themeTokens()['--sig'])->toBe('#123456');
});
