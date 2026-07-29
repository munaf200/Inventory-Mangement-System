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

    public function lot() {
        return $this->belongsTo(Purchase::class, 'lot_id');
    }

    protected static function booted()
    {
        // 1. Jab naya item invoice mein save ho, to stock MINUS karein
        static::created(function ($invoiceItem) {
            
            // Direct ID se dhoond rahe hain (bohat fast aur error-free)
            $lotItem = \App\Models\LotItem::find($invoiceItem->lot_item_id);
            
            if ($lotItem) {
                $lotItem->decrement('qty_available', $invoiceItem->qty);
            }
        });

        // 2. Agar koi item delete ho (ya poori invoice delete ho), to stock PLUS karein
        static::deleted(function ($invoiceItem) {
            
            $lotItem = \App\Models\LotItem::find($invoiceItem->lot_item_id);
            
            if ($lotItem) {
                $lotItem->increment('qty_available', $invoiceItem->qty);
            }
        });

        // 3. Agar user baad mein invoice edit karke Qty change kare!
        static::updated(function ($invoiceItem) {
            // Original aur nayi quantity ka farq nikalein
            $oldQty = $invoiceItem->getOriginal('qty');
            $newQty = $invoiceItem->qty;
            $difference = $newQty - $oldQty;

            if ($difference != 0) {
                $lotItem = \App\Models\LotItem::find($invoiceItem->lot_item_id);
                
                if ($lotItem) {
                    // Agar nayi Qty zyada hai, to mazeed minus karo. Agar kam hai, to wapas plus ho jayega
                    $lotItem->decrement('qty_available', $difference);
                }
            }
        });
    }
}
