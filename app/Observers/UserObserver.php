<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Build a structured context array for the user.
     * This pulls in related data like Biller, Warehouse, and Roles.
     */
    protected function buildContext(User $user): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $user->loadMissing(['biller', 'warehouse', 'roles']);

        return [
            'user_name'      => $user->name,
            'user_email'     => $user->email,
            'biller'         => $user->biller ? $user->biller->name : 'Not Assigned',
            'warehouse'      => $user->warehouse ? $user->warehouse->name : 'Not Assigned',
            'roles'          => $user->roles->isNotEmpty() ? $user->roles->pluck('name')->implode(', ') : 'No Role Assigned',
            'is_active'      => (bool) $user->is_active,
            'is_deleted'     => (bool) $user->is_deleted,
        ];
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        ActivityLog::create([
            'log_name'     => 'user',
            'description'  => 'created',
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($user),
                'attributes' => $user->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if this update is actually a soft‑delete (is_deleted changed to true)
        if ($user->isDirty('is_deleted') && $user->is_deleted == true) {
            $description = 'soft deleted';
        }
        // Check if it's a restoration (is_deleted changed back to false)
        elseif ($user->isDirty('is_deleted') && $user->is_deleted == false) {
            $description = 'restored';
        }
        else {
            $description = 'updated';
        }

        ActivityLog::create([
            'log_name'     => 'user',
            'description'  => $description,
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($user),
                'old'        => $user->getOriginal(),
                'attributes' => $user->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the User "deleted" event (Hard delete).
     */
    public function deleted(User $user): void
    {
        ActivityLog::create([
            'log_name'     => 'user',
            'description'  => 'permanently deleted',
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($user),
                'attributes' => $user->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        ActivityLog::create([
            'log_name'     => 'user',
            'description'  => 'restored',
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($user),
                'attributes' => $user->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        ActivityLog::create([
            'log_name'     => 'user',
            'description'  => 'force deleted',
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($user),
                'attributes' => $user->getAttributes(),
            ],
        ]);
    }
}