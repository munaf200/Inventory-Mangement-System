<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerPaymentAllocation;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    protected $guarded = [];

    public function customer() {
        return $this->belongsTo(Customer::class); //
    }

    public function allocations() {
        return $this->hasMany(CustomerPaymentAllocation::class, 'payment_id'); //
    }

    public function ledgerEntries() {
        return $this->morphMany(CustomerLedger::class, 'reference'); //
    }
}
