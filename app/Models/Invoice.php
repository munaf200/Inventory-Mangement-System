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
        // 1. Jab NAYI invoice CREATE ho (Naya Bill Bane)
        static::created(function ($invoice) {
            $customer = \App\Models\Customer::find($invoice->customer_id);

            if ($customer) {
                // Is bill ka baqi udhar nikalein (Grand Total - Amount Paid)
                $unpaidAmount = $invoice->grand_total - $invoice->amount_paid;

                // Customer ke current_balance mein naya udhar jama (add) kar dein
                $customer->increment('current_balance', $unpaidAmount);
            }
        });

        // 2. Agar invoice DELETE ho (Soft Delete ya Force Delete)
        static::deleted(function ($invoice) {
            $customer = \App\Models\Customer::find($invoice->customer_id);

            if ($customer) {
                // Bill delete hone par us bill ka udhar customer ke balance se wapas minus karein
                $unpaidAmount = $invoice->grand_total - $invoice->amount_paid;
                $customer->decrement('current_balance', $unpaidAmount);
            }
        });

        // 3. Agar invoice EDIT (UPDATE) ho
        static::updated(function ($invoice) {
            $customer = \App\Models\Customer::find($invoice->customer_id);

            if ($customer) {
                // Purana unpaid amount kya tha?
                $oldUnpaid = $invoice->getOriginal('grand_total') - $invoice->getOriginal('amount_paid');
                
                // Naya unpaid amount kya hai?
                $newUnpaid = $invoice->grand_total - $invoice->amount_paid;

                // Dono ka farq nikalein
                $difference = $newUnpaid - $oldUnpaid;

                if ($difference != 0) {
                    // Agar udhar barha to automatic plus, agar kam hua to automatic minus
                    $customer->increment('current_balance', $difference);
                }
            }
        });
    }
}
