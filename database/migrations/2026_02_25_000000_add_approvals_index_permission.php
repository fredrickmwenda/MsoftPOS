<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the approvals-index permission and grants it to Admin and Owner.
     */
    public function up(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'approvals-index', 'guard_name' => 'web']
        );

        $adminRole = Role::where('name', 'Admin')->first();
        $ownerRole = Role::where('name', 'Owner')->first();

        if ($adminRole && !$adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }
        if ($ownerRole && !$ownerRole->hasPermissionTo($permission)) {
            $ownerRole->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = Permission::where('name', 'approvals-index')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
