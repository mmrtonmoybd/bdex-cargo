<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ServiceArea;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ServiceAreaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ServiceArea');
    }

    public function view(AuthUser $authUser, ServiceArea $serviceArea): bool
    {
        return $authUser->can('View:ServiceArea');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ServiceArea');
    }

    public function update(AuthUser $authUser, ServiceArea $serviceArea): bool
    {
        return $authUser->can('Update:ServiceArea');
    }

    public function delete(AuthUser $authUser, ServiceArea $serviceArea): bool
    {
        return $authUser->can('Delete:ServiceArea');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ServiceArea');
    }

    public function restore(AuthUser $authUser, ServiceArea $serviceArea): bool
    {
        return $authUser->can('Restore:ServiceArea');
    }

    public function forceDelete(AuthUser $authUser, ServiceArea $serviceArea): bool
    {
        return $authUser->can('ForceDelete:ServiceArea');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ServiceArea');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ServiceArea');
    }

    public function replicate(AuthUser $authUser, ServiceArea $serviceArea): bool
    {
        return $authUser->can('Replicate:ServiceArea');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ServiceArea');
    }
}
