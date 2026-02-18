<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackListCustomer extends Model
{
    protected $fillable =[
        "customer_id", "total_delivery_missed", "last_delivery_miss_date", "delivery_id"
    ];
}
