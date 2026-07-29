<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LotItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
       // 1. Total Sales (Poori Farokht)
        $totalSales = Invoice::sum('grand_total') ?? 0;

        // 2. Receivables (Customers se lene wale kul paise)
        $receivables = Customer::sum('current_balance') ?? 0;

        // 3. Payables (Suppliers ko dene wale kul paise)
        // $payables = Supplier::sum('current_balance') ?? 0;

        // 4. Stock Value (Dukaan / Warehouse me mojood maal ki qimat)
        $stockValue = LotItem::sum(DB::raw('qty_available * cost_price')) ?? 0;

       // 5. Total Net Profit Logic:
        // Invoice Items ko Lot Items ke sath JOIN karke (Sale Rate - Purchase Cost) * Sold Qty nikali hai
        $grossProfitFromItems = InvoiceItem::join('lot_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
            ->sum(DB::raw('(invoice_items.rate - lot_items.cost_price) * invoice_items.qty')) ?? 0;

        // Invoice Level Discounts Minus Karein
        $totalDiscounts = Invoice::sum('discount') ?? 0;
        
        $totalProfit = $grossProfitFromItems - $totalDiscounts;
        return [
            Stat::make('Total Sales', 'Rs ' . number_format($totalSales, 0))
                ->description('Overall revenue')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'), // Green color code

            Stat::make('Total Profit', 'Rs ' . number_format($totalProfit, 0))
                ->description('Estimated profit')
                ->color('success'), // Dark Green effect

            Stat::make('Receivables', 'Rs ' . number_format($receivables, 0))
                ->description('Amount to receive')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'), // Red color code

            Stat::make('Stock Value', 'Rs ' . number_format($stockValue, 0))
                ->description('Current inventory value')
                ->color('info'), // Blue color code
        ];
    }
}
