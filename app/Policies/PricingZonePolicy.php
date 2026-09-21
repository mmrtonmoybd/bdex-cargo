<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PricingZone;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PricingZonePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PricingZone');
    }

    public function view(AuthUser $authUser, PricingZone $pricingZone): bool
    {
        return $authUser->can('View:PricingZone');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PricingZone');
    }

    public function update(AuthUser $authUser, PricingZone $pricingZone): bool
    {
        return $authUser->can('Update:PricingZone');
    }

    public function delete(AuthUser $authUser, PricingZone $pricingZone): bool
    {
        return $authUser->can('Delete:PricingZone');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PricingZone');
    }

    public function restore(AuthUser $authUser, PricingZone $pricingZone): bool
    {
        return $authUser->can('Restore:PricingZone');
    }

    public function forceDelete(AuthUser $authUser, PricingZone $pricingZone): bool
    {
        return $authUser->can('ForceDelete:PricingZone');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PricingZone');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PricingZone');
    }

    public function replicate(AuthUser $authUser, PricingZone $pricingZone): bool
    {
        return $authUser->can('Replicate:PricingZone');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PricingZone');
    }
}
