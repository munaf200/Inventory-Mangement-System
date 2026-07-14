<?php

namespace App\Models;

use App\Models\InvoiceItem;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;

class LotItem extends Model
{
    protected $guarded = [];

    public function purchase() {
        return $this->belongsTo(Purchase::class); //
    }

    public function invoiceItems() {
        return $this->hasMany(InvoiceItem::class); //
    }
}
