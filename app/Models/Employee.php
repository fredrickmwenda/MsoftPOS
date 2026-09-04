<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        "name", "image", "department_id", "email", "phone_number",
        "user_id", "staff_id", "address", "city", "country", "is_active"
    ];

    public function payroll()
    {
        return $this->hasMany(Payroll::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class); // employee's own login account
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id'); // supervisor/manager user
    }
}