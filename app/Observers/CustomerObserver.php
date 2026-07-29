<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Services\LedgerService;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        // Agar customer ka opening balance 0 se zyada hai
        if ($customer->opening_balance > 0) {
            CustomerLedger::create([
                'customer_id'      => $customer->id,
                'voucher_no'       => 'OP-BAL-' . $customer->id,
                'transaction_date' => now(),
                'description'      => 'Opening Balance Receivable',
                'type'             => 'opening_balance',
                'debit'            => $customer->opening_balance, // Customer Receivable = Debit (+)
                'credit'           => 0,
                'balance'          => 0,
                'reference_type'   => Customer::class,
                'reference_id'     => $customer->id,
            ]);

            LedgerService::recalculateCustomerLedger($customer->id);
        }
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        if ($customer->isDirty('opening_balance')) {
            $ledger = CustomerLedger::where('reference_type', Customer::class)
                ->where('reference_id', $customer->id)
                ->where('type', 'opening_balance')
                ->first();

            if ($ledger) {
                $ledger->update([
                    'debit' => $customer->opening_balance,
                ]);
            } else if ($customer->opening_balance > 0) {
                CustomerLedger::create([
                    'customer_id'      => $customer->id,
                    
                    'transaction_date' => $customer->created_at ?? now(),
                    'description'      => 'Opening Balance Receivable',
                    'type'             => 'opening_balance',
                    'debit'            => $customer->opening_balance,
                    'credit'           => 0,
                    'balance'          => 0,
                    'reference_type'   => Customer::class,
                    'reference_id'     => $customer->id,
                ]);
            }

            LedgerService::recalculateCustomerLedger($customer->id);
        }
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        //
    }
}
