<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIUsageLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ai_usage_logs';

    /**
     * Disable the updated_at timestamp since logs are append-only.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'provider',
        'skill_key',
        'request_type',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'estimated_cost',
        'status',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'estimated_cost' => 'decimal:6',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that triggered the usage log.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
