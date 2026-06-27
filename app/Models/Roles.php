<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $fillable =[
        "name", "description", "guard_name", "is_active"
    ];

    // App\Models\Role.php (or Roles.php)
public function users()
{
    return $this->belongsToMany(User::class, 'roles_user', 'roles_id', 'user_id');
}
    
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    /**
     * Check if the role has a specific permission.
     */
    public function hasPermissionTo($permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions->contains('name', $permission);
        }
        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * Assign a permission to the role.
     */
    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::firstOrCreate(['name' => $permission]);
        }
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }


    /**
     * Remove a permission from the role.
     */
    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
            if (!$permission) return;
        }
        $this->permissions()->detach($permission->id);
    }



}
