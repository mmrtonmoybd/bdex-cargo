<?php

declare(strict_types=1);

use App\Filament\Resources\ItemCategories\Pages\ListItemCategories;
use App\Filament\Resources\SurchargeRules\Pages\ListSurchargeRules;
use App\Models\User;
use Database\Seeders\PricingSeeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

use function Pest\Livewire\livewire;

/** A role holding exactly the named permissions, and nothing else. */
function roleWith(string ...$permissions): Role
{
    $role = Role::query()->create(['name' => 'role-'.Str::random(6), 'guard_name' => 'web']);

    foreach ($permissions as $permission) {
        $role->givePermissionTo(
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web'])
        );
    }

    return $role;
}

it('keeps a user with no role out of the panel', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

it('lets a user with any role reach the panel', function (): void {
    $this->actingAs(User::factory()->create()->assignRole(roleWith('ViewAny:ItemCategory')))
        ->get('/admin')
        ->assertSuccessful();
});

it('shows a resource to a role holding its permission', function (): void {
    $this->seed(PricingSeeder::class);

    $this->actingAs(User::factory()->create()->assignRole(roleWith('ViewAny:ItemCategory')));

    livewire(ListItemCategories::class)->assertSuccessful();
});

it('hides a resource from a role without its permission', function (): void {
    $this->seed(PricingSeeder::class);

    // This role can see categories but was never granted surcharge rules.
    $this->actingAs(User::factory()->create()->assignRole(roleWith('ViewAny:ItemCategory')));

    livewire(ListSurchargeRules::class)->assertForbidden();
});

it('grants access the moment a permission is added to a role, with no code change', function (): void {
    $this->seed(PricingSeeder::class);

    $role = roleWith('ViewAny:ItemCategory');
    $user = User::factory()->create()->assignRole($role);

    $this->actingAs($user);

    livewire(ListSurchargeRules::class)->assertForbidden();

    // Exactly what an admin does in Shield's own UI.
    $role->givePermissionTo(
        Permission::query()->firstOrCreate(['name' => 'ViewAny:SurchargeRule', 'guard_name' => 'web'])
    );

    $user->forgetCachedPermissions();
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    livewire(ListSurchargeRules::class)->assertSuccessful();
});
