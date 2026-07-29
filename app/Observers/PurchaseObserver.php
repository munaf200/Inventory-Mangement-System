<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\SupplierLedger;
use App\Services\LedgerService;

class PurchaseObserver
{
    /**
     * Handle the Purchase "created" event.
     */
    public function created(Purchase $purchase): void
    {
        SupplierLedger::create([
            'supplier_id'      => $purchase->supplier_id,
            'transaction_date' => $purchase->purchase_date,
            'voucher_no'       => $purchase->lot_number,
            'description'      => 'Purchase Invoice #' . $purchase->lot_number,
            'type'             => 'purchase',
            'debit'            => $purchase->amount_paid ?? 0, // Spot payment to supplier
            'credit'           => $purchase->lot_price ?? 0,  // Total Purchase Bill
            'balance'          => 0,
            'reference_type'   => Purchase::class,
            'reference_id'     => $purchase->id,
        ]);

        LedgerService::recalculateSupplierLedger($purchase->supplier_id);
    }

    /**
     * Purchase Edit (Update) Hone Par
     */
    public function updated(Purchase $purchase): void
    {
        $ledger = SupplierLedger::where('reference_type', Purchase::class)
            ->where('reference_id', $purchase->id)
            ->first();

        if ($ledger) {
            $ledger->update([
                'supplier_id'      => $purchase->supplier_id,
                'transaction_date' => $purchase->purchase_date,
                'voucher_no'       => $purchase->lot_number,
                'description'      => 'Purchase Invoice #' . $purchase->lot_number,
                'debit'            => $purchase->amount_paid ?? 0, // FIX: Spot payment changes ko sync karne ke liye debit update kia
                'credit'           => $purchase->lot_price ?? 0,   // Total purchase price sync
            ]);

            // Agar Supplier change kar diya gaya ho, to purane supplier ka ledger recalculate karein
            if ($purchase->isDirty('supplier_id')) {
                LedgerService::recalculateSupplierLedger($purchase->getOriginal('supplier_id'));
            }

            // Current Supplier ka ledger recalculate karein
            LedgerService::recalculateSupplierLedger($purchase->supplier_id);
        }
    }

    /**
     * Purchase Delete Hone Par
     */
    public function deleted(Purchase $purchase): void
    {
        SupplierLedger::where('reference_type', Purchase::class)
            ->where('reference_id', $purchase->id)
            ->delete();

        LedgerService::recalculateSupplierLedger($purchase->supplier_id);
    }

    /**
     * Handle the Purchase "restored" event.
     */
    public function restored(Purchase $purchase): void
    {
        //
    }

    /**
     * Handle the Purchase "force deleted" event.
     */
    public function forceDeleted(Purchase $purchase): void
    {
        //
    }
}
