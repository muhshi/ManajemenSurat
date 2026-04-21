<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SuratMasuk;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuratMasukPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SuratMasuk');
    }

    public function view(AuthUser $authUser, SuratMasuk $suratMasuk): bool
    {
        return $authUser->can('View:SuratMasuk');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SuratMasuk');
    }

    public function update(AuthUser $authUser, SuratMasuk $suratMasuk): bool
    {
        return $authUser->can('Update:SuratMasuk');
    }

    public function delete(AuthUser $authUser, SuratMasuk $suratMasuk): bool
    {
        return $authUser->can('Delete:SuratMasuk');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SuratMasuk');
    }

    public function restore(AuthUser $authUser, SuratMasuk $suratMasuk): bool
    {
        return $authUser->can('Restore:SuratMasuk');
    }

    public function forceDelete(AuthUser $authUser, SuratMasuk $suratMasuk): bool
    {
        return $authUser->can('ForceDelete:SuratMasuk');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SuratMasuk');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SuratMasuk');
    }

    public function replicate(AuthUser $authUser, SuratMasuk $suratMasuk): bool
    {
        return $authUser->can('Replicate:SuratMasuk');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SuratMasuk');
    }

}