<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KodeSpm;
use Illuminate\Auth\Access\HandlesAuthorization;

class KodeSpmPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KodeSpm');
    }

    public function view(AuthUser $authUser, KodeSpm $kodeSpm): bool
    {
        return $authUser->can('View:KodeSpm');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KodeSpm');
    }

    public function update(AuthUser $authUser, KodeSpm $kodeSpm): bool
    {
        return $authUser->can('Update:KodeSpm');
    }

    public function delete(AuthUser $authUser, KodeSpm $kodeSpm): bool
    {
        return $authUser->can('Delete:KodeSpm');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KodeSpm');
    }

    public function restore(AuthUser $authUser, KodeSpm $kodeSpm): bool
    {
        return $authUser->can('Restore:KodeSpm');
    }

    public function forceDelete(AuthUser $authUser, KodeSpm $kodeSpm): bool
    {
        return $authUser->can('ForceDelete:KodeSpm');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KodeSpm');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KodeSpm');
    }

    public function replicate(AuthUser $authUser, KodeSpm $kodeSpm): bool
    {
        return $authUser->can('Replicate:KodeSpm');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KodeSpm');
    }

}