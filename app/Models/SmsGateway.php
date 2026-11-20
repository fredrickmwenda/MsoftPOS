<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Your existing SmsGateway model — only the relationship + active-gateway
 * helpers have been added below. The fillable, $table and $casts lines
 * are exactly as you already had them.
 */
class SmsGateway extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'name', 'sender', 'active'];
    public $table = "sms_gateways";

    protected $casts = [
        'active' => 'boolean',
    ];

    /* -----------------------------------------------------------------
     |  Relationships  (added)
     ----------------------------------------------------------------- */

    public function messages(): HasMany
    {
        return $this->hasMany(SmsMessage::class, 'sms_gateway_id');
    }

    /* -----------------------------------------------------------------
     |  Active-gateway helpers  (added)
     ----------------------------------------------------------------- */

    /**
     * Activate this gateway and deactivate every other one — atomically.
     * This is what enforces "only one active at a time".
     */
    public function markAsActive(): void
    {
        DB::transaction(function () {
            // Deactivate the previously-active gateway (if any).
            static::where('active', true)
                  ->where('id', '!=', $this->id)
                  ->update(['active' => false]);

            $this->active = true;
            $this->save();
        });
    }

    /**
     * Convenience accessor — returns the gateway that will actually be
     * used for outbound SMS right now, or null if none is active.
     */
    public static function getActive(): ?self
    {
        return static::where('active', true)->first();
    }
}
