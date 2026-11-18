<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    use HasFactory;
    protected $fillable = ['key', 'sender', 'active'];
    public $table = "sms_gateways";
    protected $casts = [
        'active' => 'boolean',
    ];
}
