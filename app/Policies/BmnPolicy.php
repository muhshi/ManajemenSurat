<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Bmn;
use Illuminate\Auth\Access\HandlesAuthorization;

class BmnPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Bmn');
    }

    public function view(AuthUser $authUser, Bmn $bmn): bool
    {
        return $authUser->can('View:Bmn');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Bmn');
    }

    public function update(AuthUser $authUser, Bmn $bmn): bool
    {
        return $authUser->can('Update:Bmn');
    }

    public function delete(AuthUser $authUser, Bmn $bmn): bool
    {
        return $authUser->can('Delete:Bmn');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Bmn');
    }

    public function restore(AuthUser $authUser, Bmn $bmn): bool
    {
        return $authUser->can('Restore:Bmn');
    }

    public function forceDelete(AuthUser $authUser, Bmn $bmn): bool
    {
        return $authUser->can('ForceDelete:Bmn');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Bmn');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Bmn');
    }

    public function replicate(AuthUser $authUser, Bmn $bmn): bool
    {
        return $authUser->can('Replicate:Bmn');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Bmn');
    }

}