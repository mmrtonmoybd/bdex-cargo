<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PricingAgreement;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PricingAgreementPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PricingAgreement');
    }

    public function view(AuthUser $authUser, PricingAgreement $pricingAgreement): bool
    {
        return $authUser->can('View:PricingAgreement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PricingAgreement');
    }

    public function update(AuthUser $authUser, PricingAgreement $pricingAgreement): bool
    {
        return $authUser->can('Update:PricingAgreement');
    }

    public function delete(AuthUser $authUser, PricingAgreement $pricingAgreement): bool
    {
        return $authUser->can('Delete:PricingAgreement');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PricingAgreement');
    }

    public function restore(AuthUser $authUser, PricingAgreement $pricingAgreement): bool
    {
        return $authUser->can('Restore:PricingAgreement');
    }

    public function forceDelete(AuthUser $authUser, PricingAgreement $pricingAgreement): bool
    {
        return $authUser->can('ForceDelete:PricingAgreement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PricingAgreement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PricingAgreement');
    }

    public function replicate(AuthUser $authUser, PricingAgreement $pricingAgreement): bool
    {
        return $authUser->can('Replicate:PricingAgreement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PricingAgreement');
    }
}
