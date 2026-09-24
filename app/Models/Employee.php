<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        "name", 
        "image", 
        "department_id",
        "email", 
        "phone_number",
        "user_id",
        "shift_id", 
        "staff_id", 
        "address", 
        "city", 
        "country", 
        "is_active",
        //added all the others
        "is_sale_agent",
        "sale_commission_percent",
        "sales_target",
        "warehouse_id" // <-- ADD THIS HERE

    ];

        
    protected $casts = [
        'sales_target' => 'array',   // auto json_encode on save, json_decode on read
    ];

    public function payroll()
    {
        return $this->hasMany(Payroll::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class); // <-- ADD THIS RELATIONSHIP
    }

    public function user()
    {
        return $this->belongsTo(User::class); // employee's own login account
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id'); // supervisor/manager user
    }

    
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    } 

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
}