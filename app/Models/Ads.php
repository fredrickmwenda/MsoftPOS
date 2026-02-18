<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{
    use HasFactory;
    protected $fillable = [
        'staff_id',
        'input',
        'start',
        'end',
        'channel',
        'remarks'
    ];
}
