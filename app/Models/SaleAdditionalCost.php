<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleAdditionalCost extends Model
{
    use HasFactory;
    protected $table = 'sale_additional_costs';
    protected $fillable = [
        'sale_id',
        'name',
        'amount',
    ];
    
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
