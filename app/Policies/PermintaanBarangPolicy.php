<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PermintaanBarang;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermintaanBarangPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PermintaanBarang');
    }

    public function view(AuthUser $authUser, PermintaanBarang $permintaanBarang): bool
    {
        return $authUser->can('View:PermintaanBarang');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PermintaanBarang');
    }

    public function update(AuthUser $authUser, PermintaanBarang $permintaanBarang): bool
    {
        return $authUser->can('Update:PermintaanBarang');
    }

    public function delete(AuthUser $authUser, PermintaanBarang $permintaanBarang): bool
    {
        return $authUser->can('Delete:PermintaanBarang');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PermintaanBarang');
    }

    public function restore(AuthUser $authUser, PermintaanBarang $permintaanBarang): bool
    {
        return $authUser->can('Restore:PermintaanBarang');
    }

    public function forceDelete(AuthUser $authUser, PermintaanBarang $permintaanBarang): bool
    {
        return $authUser->can('ForceDelete:PermintaanBarang');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PermintaanBarang');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PermintaanBarang');
    }

    public function replicate(AuthUser $authUser, PermintaanBarang $permintaanBarang): bool
    {
        return $authUser->can('Replicate:PermintaanBarang');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PermintaanBarang');
    }

}