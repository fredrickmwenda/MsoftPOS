<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIConversation extends Model
{
    use HasFactory;
       /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ai_conversations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'mode',
        'title',
    ];



    /**
     * Scope to a specific user (replaces the old forUserAndTenant).
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }


    /**
     * Get the user that owns the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the messages for the conversation.
     * Messages are returned deterministically in chronological order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(AIMessage::class, 'conversation_id');
    }


 
}
