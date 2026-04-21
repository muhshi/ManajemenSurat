<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Surat;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuratPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Surat');
    }

    public function view(AuthUser $authUser, Surat $surat): bool
    {
        return $authUser->can('View:Surat');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Surat');
    }

    public function update(AuthUser $authUser, Surat $surat): bool
    {
        return $authUser->can('Update:Surat');
    }

    public function delete(AuthUser $authUser, Surat $surat): bool
    {
        return $authUser->can('Delete:Surat');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Surat');
    }

    public function restore(AuthUser $authUser, Surat $surat): bool
    {
        return $authUser->can('Restore:Surat');
    }

    public function forceDelete(AuthUser $authUser, Surat $surat): bool
    {
        return $authUser->can('ForceDelete:Surat');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Surat');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Surat');
    }

    public function replicate(AuthUser $authUser, Surat $surat): bool
    {
        return $authUser->can('Replicate:Surat');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Surat');
    }

}