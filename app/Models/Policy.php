<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'excerpt',
        'meta_description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Use slug for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Show only active policies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Order by sort_order, then alphabetically.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Policy $policy) {
            // Auto-generate slug if missing or empty
            if (empty($policy->slug)) {
                $policy->slug = Str::slug($policy->title);
            } else {
                $policy->slug = Str::slug($policy->slug);
            }

            // Ensure slug uniqueness by appending -2, -3, etc.
            $originalSlug = $policy->slug;
            $i = 1;
            while (
                static::where('slug', $policy->slug)
                    ->where('id', '!=', $policy->id ?? 0)
                    ->exists()
            ) {
                $policy->slug = "{$originalSlug}-{$i}";
                $i++;
            }
        });
    }
}