<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BookingEnquiry;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class BookingEnquiryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BookingEnquiry');
    }

    public function view(AuthUser $authUser, BookingEnquiry $bookingEnquiry): bool
    {
        return $authUser->can('View:BookingEnquiry');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BookingEnquiry');
    }

    public function update(AuthUser $authUser, BookingEnquiry $bookingEnquiry): bool
    {
        return $authUser->can('Update:BookingEnquiry');
    }

    public function delete(AuthUser $authUser, BookingEnquiry $bookingEnquiry): bool
    {
        return $authUser->can('Delete:BookingEnquiry');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BookingEnquiry');
    }

    public function restore(AuthUser $authUser, BookingEnquiry $bookingEnquiry): bool
    {
        return $authUser->can('Restore:BookingEnquiry');
    }

    public function forceDelete(AuthUser $authUser, BookingEnquiry $bookingEnquiry): bool
    {
        return $authUser->can('ForceDelete:BookingEnquiry');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BookingEnquiry');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BookingEnquiry');
    }

    public function replicate(AuthUser $authUser, BookingEnquiry $bookingEnquiry): bool
    {
        return $authUser->can('Replicate:BookingEnquiry');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BookingEnquiry');
    }
}
