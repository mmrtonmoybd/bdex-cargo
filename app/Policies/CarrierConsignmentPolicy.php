<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CarrierConsignment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CarrierConsignmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CarrierConsignment');
    }

    public function view(AuthUser $authUser, CarrierConsignment $carrierConsignment): bool
    {
        return $authUser->can('View:CarrierConsignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CarrierConsignment');
    }

    public function update(AuthUser $authUser, CarrierConsignment $carrierConsignment): bool
    {
        return $authUser->can('Update:CarrierConsignment');
    }

    public function delete(AuthUser $authUser, CarrierConsignment $carrierConsignment): bool
    {
        return $authUser->can('Delete:CarrierConsignment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CarrierConsignment');
    }

    public function restore(AuthUser $authUser, CarrierConsignment $carrierConsignment): bool
    {
        return $authUser->can('Restore:CarrierConsignment');
    }

    public function forceDelete(AuthUser $authUser, CarrierConsignment $carrierConsignment): bool
    {
        return $authUser->can('ForceDelete:CarrierConsignment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CarrierConsignment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CarrierConsignment');
    }

    public function replicate(AuthUser $authUser, CarrierConsignment $carrierConsignment): bool
    {
        return $authUser->can('Replicate:CarrierConsignment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CarrierConsignment');
    }
}
