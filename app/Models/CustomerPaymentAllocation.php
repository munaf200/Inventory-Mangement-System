<?php

namespace App\Models;

use App\Models\CustomerPayment;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;

class CustomerPaymentAllocation extends Model
{
    protected $guarded = [];

    public function payment() {
        return $this->belongsTo(CustomerPayment::class, 'payment_id'); //
    }

    public function invoice() {
        return $this->belongsTo(Invoice::class); //
    }
}
