<?php

namespace App\Models;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

class SupplierLedger extends Model
{
    protected $guarded = [];

    public function supplier() {
        return $this->belongsTo(Supplier::class); //
    }

    // Yeh relation Purchase aur SupplierPayment dono models ko support karega
    public function reference() {
        return $this->morphTo(); //
    }
}
