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

    protected static function booted()
    {
        // Status Auto-Adjustment based on amount_paid vs lot_price
        static::saving(function ($purchase) {
            $paid = floatval($purchase->amount_paid ?? 0);
            $total = floatval($purchase->lot_price ?? 0);

            if ($paid >= $total && $total > 0) {
                $purchase->status = 'paid';
            } elseif ($paid > 0) {
                $purchase->status = 'partial';
            } else {
                $purchase->status = 'unpaid';
            }
        });
    }
}
