<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\WeightTier;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class WeightTierPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WeightTier');
    }

    public function view(AuthUser $authUser, WeightTier $weightTier): bool
    {
        return $authUser->can('View:WeightTier');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WeightTier');
    }

    public function update(AuthUser $authUser, WeightTier $weightTier): bool
    {
        return $authUser->can('Update:WeightTier');
    }

    public function delete(AuthUser $authUser, WeightTier $weightTier): bool
    {
        return $authUser->can('Delete:WeightTier');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WeightTier');
    }

    public function restore(AuthUser $authUser, WeightTier $weightTier): bool
    {
        return $authUser->can('Restore:WeightTier');
    }

    public function forceDelete(AuthUser $authUser, WeightTier $weightTier): bool
    {
        return $authUser->can('ForceDelete:WeightTier');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WeightTier');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WeightTier');
    }

    public function replicate(AuthUser $authUser, WeightTier $weightTier): bool
    {
        return $authUser->can('Replicate:WeightTier');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WeightTier');
    }
}
