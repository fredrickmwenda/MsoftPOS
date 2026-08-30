<?php

namespace App\Observers;

use App\Models\Adjustment;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class AdjustmentObserver
{
     /* Helper method to create a standardized, readable log entry.
     */
    private function logActivity(string $action, Adjustment $model, array $properties = []): void
    {
        // You can replace $model->id with a more friendly identifier if your model has one
        // For example: $model->return_number or $model->reference_code
        $identifier = $model->id ?? 'Unknown';

        ActivityLog::create([
            'log_name'    => 'Adjustment',
            'description' => "Adjustment #{$identifier} was {$action}.",
            'subject_type'=> Adjustment::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => $properties,
        ]);
    }

    /**
     * Handle the ProductReturn "created" event.
     */
    public function created(Adjustment $model): void
    {
        $this->logActivity('created', $model, [
            'attributes' => $model->getAttributes()
        ]);
    }

    /**
     * Handle the ProductReturn "updated" event.
     */
    public function updated(Adjustment $model): void
    {
        // Get only the fields that actually changed
        $changes = $model->getChanges();
        
        // Get the original values of those specific changed fields
        $original = collect($model->getOriginal())->only(array_keys($changes))->toArray();

        $this->logActivity('updated', $model, [
            'old' => $original,
            'attributes' => $changes
        ]);
    }

    /**
     * Handle the ProductReturn "deleted" event.
     */
    public function deleted(Adjustment $model): void
    {
        $this->logActivity('deleted', $model, [
            'attributes' => $model->getAttributes()
        ]);
    }

    /**
     * Handle the ProductReturn "restored" event.
     */
    public function restored(Adjustment $model): void
    {
        $this->logActivity('restored', $model, [
            'attributes' => $model->getAttributes()
        ]);
    }

    /**
     * Handle the ProductReturn "force deleted" event.
     */
    public function forceDeleted(Adjustment $model): void
    {
        $this->logActivity('permanently deleted', $model, [
            'attributes' => $model->getAttributes()
        ]);
    }
}



