<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'ViewAny:AkunPajak', 'View:AkunPajak', 'Create:AkunPajak', 'Update:AkunPajak',
            'Delete:AkunPajak', 'DeleteAny:AkunPajak', 'Restore:AkunPajak', 'RestoreAny:AkunPajak',
            'ForceDelete:AkunPajak', 'ForceDeleteAny:AkunPajak', 'Replicate:AkunPajak', 'Reorder:AkunPajak',

            'ViewAny:KodeSpm', 'View:KodeSpm', 'Create:KodeSpm', 'Update:KodeSpm',
            'Delete:KodeSpm', 'DeleteAny:KodeSpm', 'Restore:KodeSpm', 'RestoreAny:KodeSpm',
            'ForceDelete:KodeSpm', 'ForceDeleteAny:KodeSpm', 'Replicate:KodeSpm', 'Reorder:KodeSpm',

            'View:RekapPerPihak',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }
    }

    public function down(): void
    {
        //
    }
};
