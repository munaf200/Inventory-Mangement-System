<?php

namespace App\Observers;

use App\Models\SupplierLedger;
use App\Models\SupplierPayment;

class SupplierPaymentObserver
{
    /**
     * Handle the SupplierPayment "created" event.
     */
    public function created(SupplierPayment $payment): void
    {
        $lastLedger = SupplierLedger::where('supplier_id', $payment->supplier_id)
            ->latest('id')
            ->first();

        $previousBalance = $lastLedger ? $lastLedger->balance : $payment->supplier->opening_balance;

        // 2. Naya balance calculate karo (Payment karne se udhaar kam hota hai)
        $newBalance = $previousBalance - $payment->amount_paid;

        // 3. Khate (Ledger) mein entry daal do
        SupplierLedger::create([
            'supplier_id' => $payment->supplier_id,
            'transaction_date' => $payment->payment_date,
            'description' => "Payment Sent - Voucher: {$payment->voucher_number} (" . ucfirst($payment->payment_mode) . ")",
            'type' => 'payment',
            'debit' => 0,
            'credit' => $payment->amount_paid, // Humne paise de diye
            'balance' => $newBalance,
            'reference_type' => SupplierPayment::class, // Polymorphic relation
            'reference_id' => $payment->id,
        ]);
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
