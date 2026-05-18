<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Role model that grants all permissions without using permission tables.
 * Use this when not using Spatie model_has_permissions / role_has_permissions.
 */
class Role extends SpatieRole
{
    /**
     * Grant all permissions so controllers keep working without the permission tables.
     */
    public function hasPermissionTo($permission): bool
    {
        return true;
    }
}
