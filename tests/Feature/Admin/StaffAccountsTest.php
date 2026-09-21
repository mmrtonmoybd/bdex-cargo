<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

function role(string ...$permissions): Role
{
    $role = Role::query()->create(['name' => 'role-'.Str::random(6), 'guard_name' => 'web']);

    foreach ($permissions as $permission) {
        $role->givePermissionTo(Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
    }

    return $role;
}

function administrator(): User
{
    return User::factory()->create()->assignRole(
        role('ViewAny:User', 'View:User', 'Create:User', 'Update:User', 'Delete:User')
    );
}

beforeEach(function (): void {
    $this->admin = administrator();
    $this->actingAs($this->admin);
});

it('lists staff accounts', function (): void {
    $others = User::factory()->count(2)->create();

    livewire(ListUsers::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($others->push($this->admin));
});

it('creates a staff account with a role and a hashed password', function (): void {
    $role = role('ViewAny:Order');

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Ops Desk',
            'email' => 'ops@bdexcargo.test',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'roles' => [$role->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()->where('email', 'ops@bdexcargo.test')->sole();

    expect($user->hasRole($role->name))->toBeTrue()
        ->and($user->password)->not->toBe('correct-horse-battery')
        ->and(Hash::check('correct-horse-battery', $user->password))->toBeTrue();
});

it('refuses a mistyped password confirmation', function (): void {
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Ops Desk',
            'email' => 'typo@bdexcargo.test',
            'password' => 'one-password',
            'password_confirmation' => 'another-password',
            'roles' => [role('ViewAny:Order')->id],
        ])
        ->call('create')
        ->assertHasFormErrors(['password']);
});

it('requires at least one role, since an account without one cannot sign in', function (): void {
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Nobody',
            'email' => 'nobody@bdexcargo.test',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'roles' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['roles']);
});

it('keeps the current password when the field is left empty on edit', function (): void {
    $user = User::factory()->create(['password' => 'original-password'])->assignRole(role('ViewAny:Order'));
    $hash = $user->password;

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm(['name' => 'Renamed Desk'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->name)->toBe('Renamed Desk')
        ->and($user->fresh()->password)->toBe($hash);
});

it('changes the password when a new one is given', function (): void {
    $user = User::factory()->create(['password' => 'original-password'])->assignRole(role('ViewAny:Order'));

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'password' => 'a-brand-new-password',
            'password_confirmation' => 'a-brand-new-password',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('a-brand-new-password', $user->fresh()->password))->toBeTrue();
});

it('never shows the stored password back', function (): void {
    $user = User::factory()->create(['password' => 'original-password'])->assignRole(role('ViewAny:Order'));

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertFormSet(fn (array $state): bool => blank($state['password'] ?? null));
});

it('will not let someone delete their own account', function (): void {
    expect(UserResource::canDelete($this->admin))->toBeFalse()
        ->and(UserResource::canDelete(User::factory()->create()))->toBeTrue();
});

it('will not let someone strip every role from their own account', function (): void {
    livewire(EditUser::class, ['record' => $this->admin->getRouteKey()])
        ->fillForm(['roles' => []])
        ->call('save');

    // Their roles are untouched, so they can still reach the panel.
    expect($this->admin->fresh()->roles)->not->toBeEmpty();
});

it('keeps staff accounts away from a role without the permission', function (): void {
    $this->actingAs(User::factory()->create()->assignRole(role('ViewAny:Order')));

    livewire(ListUsers::class)->assertForbidden();
});
