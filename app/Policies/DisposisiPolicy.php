<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Disposisi;
use Illuminate\Auth\Access\HandlesAuthorization;

class DisposisiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Disposisi');
    }

    public function view(AuthUser $authUser, Disposisi $disposisi): bool
    {
        return $authUser->can('View:Disposisi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Disposisi');
    }

    public function update(AuthUser $authUser, Disposisi $disposisi): bool
    {
        return $authUser->can('Update:Disposisi');
    }

    public function delete(AuthUser $authUser, Disposisi $disposisi): bool
    {
        return $authUser->can('Delete:Disposisi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Disposisi');
    }

    public function restore(AuthUser $authUser, Disposisi $disposisi): bool
    {
        return $authUser->can('Restore:Disposisi');
    }

    public function forceDelete(AuthUser $authUser, Disposisi $disposisi): bool
    {
        return $authUser->can('ForceDelete:Disposisi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Disposisi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Disposisi');
    }

    public function replicate(AuthUser $authUser, Disposisi $disposisi): bool
    {
        return $authUser->can('Replicate:Disposisi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Disposisi');
    }

}