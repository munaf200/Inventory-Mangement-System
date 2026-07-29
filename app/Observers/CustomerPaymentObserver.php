<?php

namespace App\Observers;

use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use App\Services\LedgerService;

class CustomerPaymentObserver
{
    /**
     * Handle the CustomerPayment "created" event.
     */
    public function created(CustomerPayment $payment): void
    {
        CustomerLedger::create([
            'customer_id'      => $payment->customer_id,
            'voucher_no' => $payment->receipt_number,
            'transaction_date' => $payment->payment_date,
            'description'      => 'Payment Received via ' . strtoupper($payment->payment_mode),
            'type'             => 'payment',
            'debit'            => 0,
            'credit'           => $payment->amount_received, // Wasooli = Credit (-)
            'balance'          => 0,
            'reference_type'   => CustomerPayment::class,
            'reference_id'     => $payment->id,
        ]);

        LedgerService::recalculateCustomerLedger($payment->customer_id);
    }

    /**
     * Handle the CustomerPayment "updated" event.
     */
    public function updated(CustomerPayment $payment): void
    {
        $ledger = CustomerLedger::where('reference_type', CustomerPayment::class)
            ->where('reference_id', $payment->id)
            ->first();

        if ($ledger) {
            $ledger->update([
                'customer_id'      => $payment->customer_id,
                'voucher_no'       => $payment->receipt_number, // <--- Update Voucher No
                'transaction_date' => $payment->payment_date,
                'credit'           => $payment->amount_received,
                'description'      => 'Payment Received via ' . strtoupper($payment->payment_mode) . ' (' . $payment->receipt_number . ')',
            ]);

            if ($payment->isDirty('customer_id')) {
                LedgerService::recalculateCustomerLedger($payment->getOriginal('customer_id'));
            }

            LedgerService::recalculateCustomerLedger($payment->customer_id);
        }
    }

    /**
     * Handle the CustomerPayment "deleted" event.
     */
    public function deleted(CustomerPayment $payment): void
    {
        CustomerLedger::where('reference_type', CustomerPayment::class)
            ->where('reference_id', $payment->id)
            ->delete();

        LedgerService::recalculateCustomerLedger($payment->customer_id);
    }

    /**
     * Handle the CustomerPayment "restored" event.
     */
    public function restored(CustomerPayment $customerPayment): void
    {
        //
    }

    /**
     * Handle the CustomerPayment "force deleted" event.
     */
    public function forceDeleted(CustomerPayment $customerPayment): void
    {
        //
    }
}
