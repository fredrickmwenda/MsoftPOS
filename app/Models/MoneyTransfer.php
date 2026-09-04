<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyTransfer extends Model
{
    protected $fillable = ['reference_no', 'from_account_id', 'to_account_id', 'amount', 'date', 'note'];

    public function fromAccount()
    {
    	return $this->belongsTo(Account::class);
    }

    public function toAccount()
    {
    	return $this->belongsTo('App\Models\Account');
    }
}
