<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ShippingRoute;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ShippingRoutePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ShippingRoute');
    }

    public function view(AuthUser $authUser, ShippingRoute $shippingRoute): bool
    {
        return $authUser->can('View:ShippingRoute');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ShippingRoute');
    }

    public function update(AuthUser $authUser, ShippingRoute $shippingRoute): bool
    {
        return $authUser->can('Update:ShippingRoute');
    }

    public function delete(AuthUser $authUser, ShippingRoute $shippingRoute): bool
    {
        return $authUser->can('Delete:ShippingRoute');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ShippingRoute');
    }

    public function restore(AuthUser $authUser, ShippingRoute $shippingRoute): bool
    {
        return $authUser->can('Restore:ShippingRoute');
    }

    public function forceDelete(AuthUser $authUser, ShippingRoute $shippingRoute): bool
    {
        return $authUser->can('ForceDelete:ShippingRoute');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ShippingRoute');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ShippingRoute');
    }

    public function replicate(AuthUser $authUser, ShippingRoute $shippingRoute): bool
    {
        return $authUser->can('Replicate:ShippingRoute');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ShippingRoute');
    }
}
