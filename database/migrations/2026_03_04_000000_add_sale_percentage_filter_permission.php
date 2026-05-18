<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates sale-percentage-filter permission; only users with this permission
     * can use the Filter By Percentage on Sale list, reports, and dashboard.
     * Granted to Admin and Owner by default.
     */
    public function up(): void
    {
        try {
            $permission = Permission::firstOrCreate(
                ['name' => 'sale-percentage-filter', 'guard_name' => 'web']
            );

            foreach (['Admin', 'Owner'] as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role && !$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        } catch (\Throwable $e) {
            // Permission tables not used or missing
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            $permission = Permission::where('name', 'sale-percentage-filter')->first();
            if ($permission) {
                $permission->delete();
            }
        } catch (\Throwable $e) {
            // Permission tables not used or missing
        }
    }
};
