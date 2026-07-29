<?php

namespace App\Observers;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;

class SupplierPaymentObserver
{
    /**
     * Handle the SupplierPayment "created" event.
     */
    public function created(SupplierPayment $payment): void
    {
      DB::transaction(function () use ($payment) {
            $supplier = Supplier::findOrFail($payment->supplier_id);
            
            // Calculate new balance: Old Balance - Debit (Payment)
            $newBalance = $supplier->current_balance - $payment->amount_paid;

            // 1. Insert into Ledger
            SupplierLedger::create([
                'supplier_id'      => $payment->supplier_id,
                'transaction_date' => $payment->payment_date,
                'voucher_no'       => $payment->voucher_number,
                'description'      => 'Payment Sent via ' . strtoupper($payment->payment_mode),
                'type'             => 'payment',
                'debit'            => $payment->amount_paid, // Payment is DEBIT
                'credit'           => 0,
                'balance'          => $newBalance,
                'reference_type'   => SupplierPayment::class,
                'reference_id'     => $payment->id,
            ]);

            // 2. Update Supplier Current Balance
            $supplier->update(['current_balance' => $newBalance]);
        });
    }

    /**
     * Handle the SupplierPayment "updated" event.
     */
    public function updated(SupplierPayment $supplierPayment): void
    {
        //
    }

    /**
     * Handle the SupplierPayment "deleted" event.
     */
    public function deleted(SupplierPayment $payment): void
    {
        $payment->ledgerEntries()->delete();
    }

    /**
     * Handle the SupplierPayment "restored" event.
     */
    public function restored(SupplierPayment $supplierPayment): void
    {
        //
    }

    /**
     * Handle the SupplierPayment "force deleted" event.
     */
    public function forceDeleted(SupplierPayment $supplierPayment): void
    {
        //
    }
}
