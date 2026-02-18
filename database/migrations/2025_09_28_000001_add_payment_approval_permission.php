<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddPaymentApprovalPermission extends Migration
{
    public function up()
    {
        // Create new permission
        $permission = Permission::create(['name' => 'approve-payments', 'guard' => 'web']);

        // Assign permission to Admin and Owner roles
        $adminRole = Role::where('name', 'Admin')->first();
        $ownerRole = Role::where('name', 'Owner')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }
        if ($ownerRole) {
            $ownerRole->givePermissionTo($permission);
        }
    }

    public function down()
    {
        $permission = Permission::where('name', 'approve-payments')->first();
        if ($permission) {
            $permission->delete();
        }
    }
}