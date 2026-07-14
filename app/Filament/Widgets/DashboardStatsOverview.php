<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LotItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalSales = Invoice::sum('grand_total');

        // Receivables (Customers ka bacha hua balance)
        // Yahan aap Customer ledger ya customer ke balance column ka sum le sakte hain
        $receivables = Customer::sum('opening_balance'); // Assuming you maintain a balance field

        // Stock Value (Qty Available * Cost Price)
        $stockValue = LotItem::select(DB::raw('SUM(qty_available * cost_price) as total_value'))->value('total_value');

        // Profit (Sales - Cost of sold items) 
        // Ye ek estimated profit ki example hai
        $totalProfit = Invoice::sum('sub_total') - Invoice::sum('discount'); // Apne logic se adjust karein

        return [
            Stat::make('Total Sales', 'Rs ' . number_format($totalSales, 2))
                ->description('Overall revenue')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'), // Green color code

            Stat::make('Total Profit', 'Rs ' . number_format($totalProfit, 2))
                ->description('Estimated profit')
                ->color('success'), // Dark Green effect

            Stat::make('Receivables', 'Rs ' . number_format($receivables, 2))
                ->description('Amount to receive')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'), // Red color code

            Stat::make('Stock Value', 'Rs ' . number_format($stockValue, 2))
                ->description('Current inventory value')
                ->color('info'), // Blue color code
        ];
    }
}
