<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCount extends Model
{
    protected $table = 'stock_counts';
    protected $fillable =[
        "reference_no", "warehouse_id",  "user_id", "status",  "note", 
    ];


        public function warehouse()
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function items()
    {
        return $this->hasMany(
            StockCountItem::class
        );
    }
}
