<?php

namespace App\Models;

use App\Models\Purchase;
use App\Models\SupplierLedger;
use App\Models\SupplierPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function purchases() {
        return $this->hasMany(Purchase::class); //
    }

    public function payments() {
        return $this->hasMany(SupplierPayment::class); //
    }

    public function ledgers() {
        return $this->hasMany(SupplierLedger::class); //
    }
}
