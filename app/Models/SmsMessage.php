<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * YES — you need this model.
 *
 * It powers the "messages sent" panel on the SMS dashboard. Every time
 * SettingController::sendSms() dispatches an SMS, a row is written here
 * (status = sent | failed) so you can see what went out, through which
 * gateway, and whether it succeeded.
 */
class SmsMessage extends Model
{
    use HasFactory;

    public $table = "sms_messages";

    protected $fillable = [
        'sms_gateway_id',
        'recipients',
        'message',
        'status',
        'response',
        'message_id',
        'sent_by',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     ----------------------------------------------------------------- */

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(SmsGateway::class, 'sms_gateway_id');
    }

    /**
     * Adjust to your User model path — App\User (Laravel ≤7) or
     * App\Models\User (Laravel 8+).
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'sent_by');
    }
}
