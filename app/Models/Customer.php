<?php

namespace App\Models;

use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function invoices() {
        return $this->hasMany(Invoice::class); //
    }

    public function payments() {
        return $this->hasMany(CustomerPayment::class); //
    }

    public function ledgers() {
        return $this->hasMany(CustomerLedger::class); //
    }
}
