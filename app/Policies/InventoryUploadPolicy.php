<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InventoryUpload;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryUploadPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InventoryUpload');
    }

    public function view(AuthUser $authUser, InventoryUpload $inventoryUpload): bool
    {
        return $authUser->can('View:InventoryUpload');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InventoryUpload');
    }

    public function update(AuthUser $authUser, InventoryUpload $inventoryUpload): bool
    {
        return $authUser->can('Update:InventoryUpload');
    }

    public function delete(AuthUser $authUser, InventoryUpload $inventoryUpload): bool
    {
        return $authUser->can('Delete:InventoryUpload');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InventoryUpload');
    }

    public function restore(AuthUser $authUser, InventoryUpload $inventoryUpload): bool
    {
        return $authUser->can('Restore:InventoryUpload');
    }

    public function forceDelete(AuthUser $authUser, InventoryUpload $inventoryUpload): bool
    {
        return $authUser->can('ForceDelete:InventoryUpload');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InventoryUpload');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InventoryUpload');
    }

    public function replicate(AuthUser $authUser, InventoryUpload $inventoryUpload): bool
    {
        return $authUser->can('Replicate:InventoryUpload');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InventoryUpload');
    }

}