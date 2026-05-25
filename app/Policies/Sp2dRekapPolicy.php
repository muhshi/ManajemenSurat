<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Sp2dRekap;
use Illuminate\Auth\Access\HandlesAuthorization;

class Sp2dRekapPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Sp2dRekap');
    }

    public function view(AuthUser $authUser, Sp2dRekap $sp2dRekap): bool
    {
        return $authUser->can('View:Sp2dRekap');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Sp2dRekap');
    }

    public function update(AuthUser $authUser, Sp2dRekap $sp2dRekap): bool
    {
        return $authUser->can('Update:Sp2dRekap');
    }

    public function delete(AuthUser $authUser, Sp2dRekap $sp2dRekap): bool
    {
        return $authUser->can('Delete:Sp2dRekap');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Sp2dRekap');
    }

    public function restore(AuthUser $authUser, Sp2dRekap $sp2dRekap): bool
    {
        return $authUser->can('Restore:Sp2dRekap');
    }

    public function forceDelete(AuthUser $authUser, Sp2dRekap $sp2dRekap): bool
    {
        return $authUser->can('ForceDelete:Sp2dRekap');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Sp2dRekap');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Sp2dRekap');
    }

    public function replicate(AuthUser $authUser, Sp2dRekap $sp2dRekap): bool
    {
        return $authUser->can('Replicate:Sp2dRekap');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Sp2dRekap');
    }

}