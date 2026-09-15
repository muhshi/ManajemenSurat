<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AkunPajak;
use Illuminate\Auth\Access\HandlesAuthorization;

class AkunPajakPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AkunPajak');
    }

    public function view(AuthUser $authUser, AkunPajak $akunPajak): bool
    {
        return $authUser->can('View:AkunPajak');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AkunPajak');
    }

    public function update(AuthUser $authUser, AkunPajak $akunPajak): bool
    {
        return $authUser->can('Update:AkunPajak');
    }

    public function delete(AuthUser $authUser, AkunPajak $akunPajak): bool
    {
        return $authUser->can('Delete:AkunPajak');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AkunPajak');
    }

    public function restore(AuthUser $authUser, AkunPajak $akunPajak): bool
    {
        return $authUser->can('Restore:AkunPajak');
    }

    public function forceDelete(AuthUser $authUser, AkunPajak $akunPajak): bool
    {
        return $authUser->can('ForceDelete:AkunPajak');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AkunPajak');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AkunPajak');
    }

    public function replicate(AuthUser $authUser, AkunPajak $akunPajak): bool
    {
        return $authUser->can('Replicate:AkunPajak');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AkunPajak');
    }

}