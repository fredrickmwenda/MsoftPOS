<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        ActivityLog::create([
            'log_name'    => 'user',
            'description' => 'created',
            'subject_type'=> User::class,
            'subject_id'  => $user->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            $description = 'deleted';
        }
        // Check if it's a restoration (is_deleted changed back to false)
        elseif ($user->isDirty('is_deleted') && $user->is_deleted == false) {
            $description = 'restored';
        }
        else {
            $description = 'updated';
        }

        ActivityLog::create([
            'log_name'    => 'user',
            'description' => $description,
            'subject_type'=> User::class,
            'subject_id'  => $user->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $user->getOriginal(),
                'attributes' => $user->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the User "deleted" event (only fires if you use ->delete() – you don’t, but we keep it).
     */
    public function deleted(User $user): void
    {
        ActivityLog::create([
            'log_name'    => 'user',
            'description' => 'permanently deleted',
            'subject_type'=> User::class,
            'subject_id'  => $user->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $user->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the User "restored" event (only with SoftDeletes trait – not used here).
     */
    public function restored(User $user): void
    {
        ActivityLog::create([
            'log_name'    => 'user',
            'description' => 'restored',
            'subject_type'=> User::class,
            'subject_id'  => $user->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'user',
            'description' => 'force deleted',
            'subject_type'=> User::class,
            'subject_id'  => $user->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $user->getAttributes(),
            ],
        ]);
    }
}