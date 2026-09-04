<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Import the User model

class Biller extends Model
{
    protected $fillable =[
        "name", "image", "company_name", "vat_number",
        "email", "phone_number", "address", "city",
        "state", "postal_code", "country", "is_active"
    ];

    public function sale()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the users assigned to this biller.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}