<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\LotItem;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $guarded = [];

    public function invoice() {
        return $this->belongsTo(Invoice::class); //
    }

    public function lotItem() {
        return $this->belongsTo(LotItem::class); //
    }
}
