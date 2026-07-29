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
    
    protected static function booted()
    {
      static::saving(function ($invoice) {
            if ($invoice->amount_paid >= $invoice->grand_total && $invoice->grand_total > 0) {
                $invoice->status = 'paid';
                if ($invoice->payment_mode === 'credit') {
                    $invoice->payment_mode = 'cash'; 
                }
            } elseif ($invoice->amount_paid > 0 && $invoice->amount_paid < $invoice->grand_total) {
                $invoice->status = 'partial';
            } else {
                $invoice->status = 'unpaid';
                $invoice->payment_mode = 'credit';
            }
        });
    }
}
