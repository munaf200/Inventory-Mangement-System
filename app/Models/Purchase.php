<?php

namespace App\Models;

use App\Models\LotItem;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierPaymentAllocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function supplier() {
        return $this->belongsTo(Supplier::class); //
    }

    public function lotItems() {
        return $this->hasMany(LotItem::class); //
    }

    public function paymentAllocations() {
        return $this->hasMany(SupplierPaymentAllocation::class); //
    }

    // Ledger mein entry track karne ke liye polymorphic relation
    public function ledgerEntries() {
        return $this->morphMany(SupplierLedger::class, 'reference'); //
    }
}
