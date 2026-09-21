<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FxRate;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FxRatePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FxRate');
    }

    public function view(AuthUser $authUser, FxRate $fxRate): bool
    {
        return $authUser->can('View:FxRate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FxRate');
    }

    public function update(AuthUser $authUser, FxRate $fxRate): bool
    {
        return $authUser->can('Update:FxRate');
    }

    public function delete(AuthUser $authUser, FxRate $fxRate): bool
    {
        return $authUser->can('Delete:FxRate');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FxRate');
    }

    public function restore(AuthUser $authUser, FxRate $fxRate): bool
    {
        return $authUser->can('Restore:FxRate');
    }

    public function forceDelete(AuthUser $authUser, FxRate $fxRate): bool
    {
        return $authUser->can('ForceDelete:FxRate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FxRate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FxRate');
    }

    public function replicate(AuthUser $authUser, FxRate $fxRate): bool
    {
        return $authUser->can('Replicate:FxRate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FxRate');
    }
}
