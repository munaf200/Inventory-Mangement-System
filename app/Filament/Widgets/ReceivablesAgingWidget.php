<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class ReceivablesAgingWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.receivables-aging-widget';

    protected int | string | array $columnSpan = 'full';

    public array $filters = [];

    #[On('update-analytics-filters')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function getAgingCalculations(): array
    {
        // Customer relation ko eager load kiya taake N+1 query issue na aaye
        $invoices = Invoice::with('customer')
            ->whereIn('status', ['unpaid', 'partial'])
            ->get();

        $safeZone = 0;
        $reminderZone = 0;
        $dangerZone = 0;

        // Har range ke liye customers aur invoices ka data store karne ke liye arrays
        $safeItems = [];
        $reminderItems = [];
        $dangerItems = [];

        foreach ($invoices as $invoice) {
            // $daysOld = now()->diffInDays($invoice->invoice_date);
            $daysOld = (int) abs(now()->startOfDay()->diffInDays(Carbon::parse($invoice->invoice_date)->startOfDay()));
            $pendingAmount = $invoice->grand_total;

            // Customer name nikalne ke liye multiple safe fallbacks lagaye hain
            $customerName = optional($invoice->customer)->name
                ?? $invoice->customer_name
                ?? $invoice->client_name
                ?? 'Unknown Customer';

            $invoiceNo = $invoice->invoice_number ?? $invoice->id;

            $itemData = [
                'customer' => $customerName,
                'invoice_no' => $invoiceNo,
                'amount' => $pendingAmount,
                'days' => $daysOld,
            ];

            if ($daysOld <= 30) {
                $safeZone += $pendingAmount;
                $safeItems[] = $itemData;
            } elseif ($daysOld <= 60) {
                $reminderZone += $pendingAmount;
                $reminderItems[] = $itemData;
            } else {
                $dangerZone += $pendingAmount;
                $dangerItems[] = $itemData;
            }
        }

        return [
            'safe' => [
                'total' => $safeZone,
                'items' => $safeItems,
            ],
            'reminder' => [
                'total' => $reminderZone,
                'items' => $reminderItems,
            ],
            'danger' => [
                'total' => $dangerZone,
                'items' => $dangerItems,
            ],
        ];
    }
}