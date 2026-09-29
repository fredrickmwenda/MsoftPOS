<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class PayrollTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'work_duration',
        'duration_unit',
        'amount_per_duration',
        'items',
        'description',
        'is_default',
    ];

    protected $casts = [
        'work_duration'       => 'decimal:2',
        'amount_per_duration' => 'decimal:2',
        'items'               => 'array',     // ✅ JSON items
        'is_default'          => 'boolean',
    ];

    /* ----------------------------------------------------------
     |  Relationships
     +--------------------------------------------------------- */
    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'payroll_template_tax');
    }

    /* ----------------------------------------------------------
     |  Helpers — work with items as a Collection (not raw array)
     +--------------------------------------------------------- */
    public function getItemsCollection(): Collection
    {
        return collect($this->items ?? []);
    }

    public function calculateGross(float $workDuration = null): float
    {
        $duration = $workDuration ?? (float) $this->work_duration;
        $perDuration = (float) $this->amount_per_duration;
        
        $itemsTotal = 0;
        foreach ($this->getItemsCollection() as $item) {
            $amount = (float) ($item['amount'] ?? 0);
            $itemsTotal += ($item['type'] ?? 'allowance') === 'allowance' ? $amount : -$amount;
        }
        
        return ($perDuration * $duration) + $itemsTotal;
    }
}