<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class CustomerLedger extends Model
{
    protected $guarded = [];

    public function customer() {
        return $this->belongsTo(Customer::class); //
    }

    // Yeh relation Invoice aur CustomerPayment dono models ko support karega
    public function reference() {
        return $this->morphTo(); //
    }
}
