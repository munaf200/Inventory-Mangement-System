<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
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
        DB::transaction(function () use ($payment) {
            // 1. Supplier ka current_balance wapas barhayein (Add back paid amount)
            $supplier = Supplier::find($payment->supplier_id);
            if ($supplier) {
                $supplier->increment('current_balance', $payment->amount_paid);
            }

            // 2. Purchase Allocations Revert Karein
            $allocations = SupplierPaymentAllocation::where('supplier_payment_id', $payment->id)->get();

            foreach ($allocations as $allocation) {
                $purchase = Purchase::find($allocation->purchase_id);
                if ($purchase) {
                    $restoredBalance = $purchase->balance_amount + $allocation->amount;
                    
                    // Agar balance dobara total bill jitna ya us se zyada ho gaya hai to 'unpaid', warna 'partial'
                    $status = ($restoredBalance >= ($purchase->total_amount ?? $restoredBalance)) ? 'unpaid' : 'partial';

                    $purchase->updateQuietly([
                        'balance_amount' => $restoredBalance,
                        'status'         => $status,
                    ]);
                }
                
                // Allocation record delete karein
                $allocation->delete();
            }

            // 3. Ledger Entry Delete Karein
            SupplierLedger::where('reference_type', SupplierPayment::class)
                ->where('reference_id', $payment->id)
                ->delete();
            
            // Note: Agar aap ne Supplier ke liye bhi LedgerService::recalculateSupplierLedger() banaya hua hai 
            // to yahan usko call kar sakte hain.
        });
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
