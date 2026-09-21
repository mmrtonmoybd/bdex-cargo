<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SurchargeRule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SurchargeRulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SurchargeRule');
    }

    public function view(AuthUser $authUser, SurchargeRule $surchargeRule): bool
    {
        return $authUser->can('View:SurchargeRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SurchargeRule');
    }

    public function update(AuthUser $authUser, SurchargeRule $surchargeRule): bool
    {
        return $authUser->can('Update:SurchargeRule');
    }

    public function delete(AuthUser $authUser, SurchargeRule $surchargeRule): bool
    {
        return $authUser->can('Delete:SurchargeRule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SurchargeRule');
    }

    public function restore(AuthUser $authUser, SurchargeRule $surchargeRule): bool
    {
        return $authUser->can('Restore:SurchargeRule');
    }

    public function forceDelete(AuthUser $authUser, SurchargeRule $surchargeRule): bool
    {
        return $authUser->can('ForceDelete:SurchargeRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SurchargeRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SurchargeRule');
    }

    public function replicate(AuthUser $authUser, SurchargeRule $surchargeRule): bool
    {
        return $authUser->can('Replicate:SurchargeRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SurchargeRule');
    }
}
