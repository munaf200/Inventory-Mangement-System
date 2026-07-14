<?php

namespace App\Models;

use App\Models\Purchase;
use App\Models\SupplierPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPaymentAllocation extends Model
{
    // use SoftDeletes;
    protected $guarded = [];

    public function payment() {
        return $this->belongsTo(SupplierPayment::class, 'supplier_payment_id'); //
    }

    public function purchase() {
        return $this->belongsTo(Purchase::class); //
    }
}
