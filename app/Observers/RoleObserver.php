<?php

namespace App\Observers;

use App\Models\Roles;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoleObserver
{
    /**
     * Handle the Role "updated" event - invalidate permission cache for all users with this role
     */
    public function updated(Roles $role): void
    {
        // Clear all permission caches when a role is updated
        // This is a broad approach - you may want to only clear for specific users with this role
        $users = DB::table('users')->where('role_id', $role->id)->get();
        foreach ($users as $user) {
            Cache::forget('user_permissions_' . $role->id);
        }
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Roles $role): void
    {
        // Role was deleted, clear related caches
        Cache::forget('user_permissions_' . $role->id);
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Roles $role): void
    {
        // Clear cache when role is restored
        Cache::forget('user_permissions_' . $role->id);
    }

    /**
     * Handle the Role "force deleted" event.
     */
    public function forceDeleted(Roles $role): void
    {
        // Clear cache when role is force deleted
        Cache::forget('user_permissions_' . $role->id);
    }
}
