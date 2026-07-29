<?php

namespace App\Observers;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Services\LedgerService;

class SupplierObserver
{
    /**
     * Handle the Supplier "created" event.
     */
    public function created(Supplier $supplier): void
    {
        if ($supplier->opening_balance > 0) {
            SupplierLedger::create([
                'supplier_id'      => $supplier->id,
                'transaction_date' => $supplier->created_at->toDateString(),
                'voucher_no'       => 'OP-BAL-' . $supplier->id,
                'description'      => 'Opening Balance Payable',
                'type'             => 'opening_balance',
                'debit'            => 0,
                'credit'           => $supplier->opening_balance, // Payable barhta hai (+)
                'balance'          => $supplier->opening_balance,
                'reference_type'   => Supplier::class,
                'reference_id'     => $supplier->id,
            ]);

            // Ledger running balance calculation
            // LedgerService::recalculateSupplierLedger($supplier->id);
        }
    }

    /**
     * Handle the Supplier "updated" event.
     */
    public function updated(Supplier $supplier): void
    {
        if ($supplier->isDirty('opening_balance')) {
            SupplierLedger::updateOrCreate(
                [
                    'reference_type' => Supplier::class,
                    'reference_id'   => $supplier->id,
                    'type'           => 'opening_balance',
                ],
                [
                    'supplier_id'      => $supplier->id,
                    'transaction_date' => $supplier->created_at->toDateString(),
                    'voucher_no'       => 'OP-BAL-' . $supplier->id,
                    'description'      => 'Opening Balance Payable',
                    'credit'           => $supplier->opening_balance,
                    'debit'            => 0,
                ]
            );

            LedgerService::recalculateSupplierLedger($supplier->id);
        }
    }

    /**
     * Handle the Supplier "deleted" event.
     */
    public function deleted(Supplier $supplier): void
    {
        //
    }

    /**
     * Handle the Supplier "restored" event.
     */
    public function restored(Supplier $supplier): void
    {
        //
    }

    /**
     * Handle the Supplier "force deleted" event.
     */
    public function forceDeleted(Supplier $supplier): void
    {
        //
    }
}
