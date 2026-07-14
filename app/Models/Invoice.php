<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerPaymentAllocation;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];

    public function customer() {
        return $this->belongsTo(Customer::class); //
    }

    public function items() {
        return $this->hasMany(InvoiceItem::class); //
    }

    public function paymentAllocations() {
        return $this->hasMany(CustomerPaymentAllocation::class); //
    }

    // Ledger mein track karne ke liye polymorphic relation
    public function ledgerEntries() {
        return $this->morphMany(CustomerLedger::class, 'reference'); //
    }
}
