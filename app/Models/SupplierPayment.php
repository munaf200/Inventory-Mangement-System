<?php

namespace App\Models;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierPaymentAllocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPayment extends Model
{
    // use SoftDeletes;
    protected $guarded = [];

    public function supplier() {
        return $this->belongsTo(Supplier::class); //
    }

    public function allocations() {
        return $this->hasMany(SupplierPaymentAllocation::class); //
    }

    public function ledgerEntries() {
        return $this->morphMany(SupplierLedger::class, 'reference'); //
    }
}
