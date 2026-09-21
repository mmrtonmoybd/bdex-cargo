<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TvSizeBand;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TvSizeBandPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TvSizeBand');
    }

    public function view(AuthUser $authUser, TvSizeBand $tvSizeBand): bool
    {
        return $authUser->can('View:TvSizeBand');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TvSizeBand');
    }

    public function update(AuthUser $authUser, TvSizeBand $tvSizeBand): bool
    {
        return $authUser->can('Update:TvSizeBand');
    }

    public function delete(AuthUser $authUser, TvSizeBand $tvSizeBand): bool
    {
        return $authUser->can('Delete:TvSizeBand');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TvSizeBand');
    }

    public function restore(AuthUser $authUser, TvSizeBand $tvSizeBand): bool
    {
        return $authUser->can('Restore:TvSizeBand');
    }

    public function forceDelete(AuthUser $authUser, TvSizeBand $tvSizeBand): bool
    {
        return $authUser->can('ForceDelete:TvSizeBand');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TvSizeBand');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TvSizeBand');
    }

    public function replicate(AuthUser $authUser, TvSizeBand $tvSizeBand): bool
    {
        return $authUser->can('Replicate:TvSizeBand');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TvSizeBand');
    }
}
