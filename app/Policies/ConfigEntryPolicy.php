<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ConfigEntry;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ConfigEntryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ConfigEntry');
    }

    public function view(AuthUser $authUser, ConfigEntry $configEntry): bool
    {
        return $authUser->can('View:ConfigEntry');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ConfigEntry');
    }

    public function update(AuthUser $authUser, ConfigEntry $configEntry): bool
    {
        return $authUser->can('Update:ConfigEntry');
    }

    public function delete(AuthUser $authUser, ConfigEntry $configEntry): bool
    {
        return $authUser->can('Delete:ConfigEntry');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ConfigEntry');
    }

    public function restore(AuthUser $authUser, ConfigEntry $configEntry): bool
    {
        return $authUser->can('Restore:ConfigEntry');
    }

    public function forceDelete(AuthUser $authUser, ConfigEntry $configEntry): bool
    {
        return $authUser->can('ForceDelete:ConfigEntry');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ConfigEntry');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ConfigEntry');
    }

    public function replicate(AuthUser $authUser, ConfigEntry $configEntry): bool
    {
        return $authUser->can('Replicate:ConfigEntry');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ConfigEntry');
    }
}
