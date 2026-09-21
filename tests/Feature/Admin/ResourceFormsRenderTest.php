<?php

declare(strict_types=1);

use App\Enums\CalculationType;
use App\Enums\ConditionField;
use App\Enums\ConditionOperator;
use App\Enums\PricingModel;
use App\Filament\Resources\ItemCategories\Pages\EditItemCategory;
use App\Filament\Resources\SurchargeRules\Pages\EditSurchargeRule;
use App\Models\ItemCategory;
use App\Models\ShippingRoute;
use App\Models\SurchargeRule;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

/**
 * Edit forms hydrate enum-cast fields as enum instances, while create forms start
 * with raw strings. Anything reading that state as a string fatals on edit — which
 * is exactly what happened on the surcharge rule form in the panel.
 */
function editor(string ...$permissions): User
{
    $role = Role::query()->create(['name' => 'role-'.Str::random(6), 'guard_name' => 'web']);

    foreach ($permissions as $permission) {
        $role->givePermissionTo(Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
    }

    return User::factory()->create()->assignRole($role);
}

it('opens a surcharge rule for editing, whatever its calculation type', function (CalculationType $type): void {
    $this->actingAs(editor('ViewAny:SurchargeRule', 'Update:SurchargeRule', 'View:SurchargeRule'));

    $rule = SurchargeRule::query()->create([
        'code' => 'rule_'.Str::random(6),
        'label' => 'Test fee',
        'calculation_type' => $type,
        'amount' => 15,
        'condition_field' => ConditionField::DeclaredValue,
        'condition_operator' => ConditionOperator::GreaterThan,
        'condition_value' => '100',
        'is_active' => true,
    ]);

    livewire(EditSurchargeRule::class, ['record' => $rule->getRouteKey()])
        ->assertSuccessful()
        ->assertFormSet(['code' => $rule->code]);
})->with(CalculationType::cases());

it('opens a boolean-condition rule for editing', function (): void {
    $this->actingAs(editor('ViewAny:SurchargeRule', 'Update:SurchargeRule', 'View:SurchargeRule'));

    $rule = SurchargeRule::query()->create([
        'code' => 'non_garment_'.Str::random(4),
        'label' => 'Non-garments handling charge',
        'calculation_type' => CalculationType::PerKg,
        'amount' => 2,
        'minimum_amount' => 20,
        'condition_field' => ConditionField::IsNonGarment,
        'condition_operator' => ConditionOperator::IsTrue,
        'is_active' => true,
    ]);

    livewire(EditSurchargeRule::class, ['record' => $rule->getRouteKey()])->assertSuccessful();
});

it('opens an item category for editing, whatever its pricing model', function (PricingModel $model): void {
    $this->actingAs(editor('ViewAny:ItemCategory', 'Update:ItemCategory', 'View:ItemCategory'));

    $category = ItemCategory::query()->create([
        'shipping_route_id' => ShippingRoute::factory()->create()->id,
        'name' => 'Category '.Str::random(5),
        'pricing_model' => $model,
        'flat_fee_amount' => $model->usesFlatFeeAmount() ? 100 : null,
        'is_active' => true,
    ]);

    livewire(EditItemCategory::class, ['record' => $category->getRouteKey()])
        ->assertSuccessful()
        ->assertFormSet(['name' => $category->name]);
})->with(PricingModel::cases());
