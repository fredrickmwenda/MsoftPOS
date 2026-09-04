<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCardRecharge extends Model
{
    protected $table = 'gift_card_recharges';

    protected $fillable = [
        "gift_card_id", "amount", "user_id"
    ];

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}