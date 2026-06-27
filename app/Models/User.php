<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'community', 'location', 'region', 'status',
        'email', 'password', 'phone', 'company_name',
        'biller_id', 'warehouse_id', 'is_active', 'is_deleted'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function roles()
    {
        return $this->belongsToMany(
            Roles::class,      // or the correct model name
            'roles_user',      // pivot table name
            'user_id',         // foreign key from User
            'roles_id'         // foreign key from Roles (use 'role_id' if that's the actual column)
        );
    }
    // All permissions aggregated from all roles
    public function getAllPermissions()
    {
        // Assumes Roles model has a permissions() relationship (belongsToMany)
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions.*.name')
            ->flatten()
            ->unique()
            ->toArray();
    }

    // Permission check: returns true if any role has the permission
    public function hasPermissionTo($permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($q) use ($permission) {
                $q->where('name', $permission);
            })->exists();
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function holiday()
    {
        return $this->hasMany('App\Models\Holiday');
    }
}
