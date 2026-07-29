<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\SupplierLedger;

class LedgerService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public static function recalculateSupplierLedger($supplierId)
    {
        $ledgers = SupplierLedger::where('supplier_id', $supplierId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;

        foreach ($ledgers as $ledger) {
            // Credit increases payable (+), Debit decreases payable (-)
            $runningBalance = $runningBalance + $ledger->credit - $ledger->debit;
            
            $ledger->updateQuietly([
                'balance' => $runningBalance
            ]);
        }

        // Update overall supplier current balance
        Supplier::where('id', $supplierId)->update([
            'current_balance' => $runningBalance
        ]);
    }

    public static function recalculateCustomerLedger($customerId): void
    {
        $ledgers = CustomerLedger::where('customer_id', $customerId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;

        foreach ($ledgers as $ledger) {
            $runningBalance += ($ledger->debit - $ledger->credit);

            $ledger->updateQuietly([
                'balance' => $runningBalance,
            ]);
        }

        Customer::where('id', $customerId)->update([
            'current_balance' => $runningBalance,
        ]);

        // Opening Balance & Invoices status update karein
        self::recalculateInvoiceStatuses($customerId);
    }

    /**
     * Opening Balance Pehle Cover Karein + Invoices FIFO Clear Karein + Mode Change Karein
     */
    // public static function recalculateInvoiceStatuses($customerId): void
    // {
    //     $customer = Customer::find($customerId);
    //     if (!$customer) return;

    //     // 1. Customer ki saari payments tareeq ke hisaab se fetch karein
    //     $payments = CustomerPayment::where('customer_id', $customerId)
    //         ->orderBy('payment_date', 'asc')
    //         ->orderBy('id', 'asc')
    //         ->get();

    //     // 2. Pehle Opening Balance deduct (cover) karein
    //     $remainingOpBal = $customer->opening_balance ?? 0;
    //     $paymentPool = [];

    //     foreach ($payments as $p) {
    //         $amt = $p->amount_received;

    //         // Step A: Pehle Opening Balance me se katoti karein
    //         if ($remainingOpBal > 0) {
    //             $deduct = min($remainingOpBal, $amt);
    //             $remainingOpBal -= $deduct;
    //             $amt -= $deduct; // Baaqi bachi hui payment invoices ke liye use hogi
    //         }

    //         // Step B: Agar Opening balance cover hone ke baad rakam bachi hai
    //         if ($amt > 0) {
    //             // Payment mode ko InvoiceEnum ['cash', 'bank', 'credit'] mein adjust karein
    //             $mode = match (strtolower($p->payment_mode)) {
    //                 'cash' => 'cash',
    //                 'bank', 'bank transfer', 'cheque' => 'bank',
    //                 default => 'cash',
    //             };

    //             $paymentPool[] = [
    //                 'amount' => $amt,
    //                 'mode'   => $mode,
    //             ];
    //         }
    //     }

    //     // 3. Purani se nayi invoices par baaqi payments apply karein
    //     $invoices = Invoice::where('customer_id', $customerId)
    //         ->orderBy('invoice_date', 'asc')
    //         ->orderBy('id', 'asc')
    //         ->get();

    //     foreach ($invoices as $invoice) {
    //         $invoiceTotal = $invoice->grand_total;
    //         $invoicePaid = 0;
    //         $lastModeUsed = 'credit'; // Default jab tak payment na mile

    //         // Jab tak invoice poori nahi hoti aur payment pool mein paise hain
    //         while ($invoiceTotal > $invoicePaid && !empty($paymentPool)) {
    //             $currentPayment = &$paymentPool[0];
    //             $needed = $invoiceTotal - $invoicePaid;

    //             if ($currentPayment['amount'] <= $needed) {
    //                 $invoicePaid += $currentPayment['amount'];
    //                 $lastModeUsed = $currentPayment['mode'];
    //                 array_shift($paymentPool); // Ye payment chunk poora use ho gaya
    //             } else {
    //                 $invoicePaid += $needed;
    //                 $currentPayment['amount'] -= $needed; // Partial chunk use hua
    //                 $lastModeUsed = $currentPayment['mode'];
    //             }
    //         }

    //         // 4. Invoice Table Update (Status, Amount Paid, & Payment Mode)
    //         if ($invoicePaid == 0) {
    //             // Invoice Unpaid hai (Udhaar par hi hai)
    //             $invoice->updateQuietly([
    //                 'amount_paid'  => 0,
    //                 'status'       => 'unpaid',
    //                 'payment_mode' => 'credit',
    //             ]);
    //         } elseif ($invoicePaid >= $invoiceTotal) {
    //             // Invoice Fully Paid ho gayi (Mode Cash/Bank update hoga)
    //             $invoice->updateQuietly([
    //                 'amount_paid'  => $invoiceTotal,
    //                 'status'       => 'paid',
    //                 'payment_mode' => $lastModeUsed,
    //             ]);
    //         } else {
    //             // Invoice Parital Pay hui (Mode Cash/Bank update hoga)
    //             $invoice->updateQuietly([
    //                 'amount_paid'  => $invoicePaid,
    //                 'status'       => 'partial',
    //                 'payment_mode' => $lastModeUsed,
    //             ]);
    //         }
    //     }
    // }
    public static function recalculateInvoiceStatuses($customerId)
{
    $invoices = Invoice::where('customer_id', $customerId)
        ->orderBy('invoice_date', 'asc')
        ->get();

    foreach ($invoices as $invoice) {
        // Invoice par pehle se jo spot payment hui hai wo lein
        $spotPaid = floatval($invoice->amount_paid);
        $grandTotal = floatval($invoice->grand_total);

        // Status Determine Karein
        if ($spotPaid >= $grandTotal && $grandTotal > 0) {
            $status = 'paid';
        } elseif ($spotPaid > 0) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
        }

        // Invoice ko disturb kiye bagair status sync karein
        $invoice->updateQuietly([
            'status' => $status,
        ]);
    }
}
}
