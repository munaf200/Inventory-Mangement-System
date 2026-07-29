<?php

namespace App\Observers;

use App\Models\CustomerLedger;
use App\Models\Invoice;
use App\Services\LedgerService;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        CustomerLedger::create([
            'customer_id'      => $invoice->customer_id,
            'voucher_no'       => $invoice->invoice_number,
            'transaction_date' => $invoice->invoice_date,
            'description'      => 'Sale Invoice #' . $invoice->invoice_number,
            'type'             => 'invoice',
            'debit'            => $invoice->grand_total,
            'credit'           => $invoice->amount_paid ?? 0,
            'balance'          => 0,
            'reference_type'   => Invoice::class,
            'reference_id'     => $invoice->id,
        ]);

        LedgerService::recalculateCustomerLedger($invoice->customer_id);
    }

    /**
     * Invoice Edit (Update) Hone Par
     */
    public function updated(Invoice $invoice): void
    {
        $ledger = CustomerLedger::where('reference_type', Invoice::class)
            ->where('reference_id', $invoice->id)
            ->first();

        if ($ledger) {
            $ledger->update([
                'customer_id'      => $invoice->customer_id,
                'voucher_no'       => $invoice->invoice_number,
                'transaction_date' => $invoice->invoice_date,
                'description'      => 'Sale Invoice #' . $invoice->invoice_number,
                'debit'            => $invoice->grand_total,
                'credit'           => $invoice->amount_paid ?? 0, // FIX: Spot payment changes ko sync karne ke liye credit update kia
            ]);

            // Agar customer change kar diya gaya ho, to purane customer ka balance recalculate karein
            if ($invoice->isDirty('customer_id')) {
                LedgerService::recalculateCustomerLedger($invoice->getOriginal('customer_id'));
            }

            // Naye / Current Customer ka balance recalculate karein
            LedgerService::recalculateCustomerLedger($invoice->customer_id);
        }
    }

    /**
     * Invoice Delete Hone Par
     */
    public function deleted(Invoice $invoice): void
    {
        CustomerLedger::where('reference_type', Invoice::class)
            ->where('reference_id', $invoice->id)
            ->delete();

        LedgerService::recalculateCustomerLedger($invoice->customer_id);
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        //
    }
}
