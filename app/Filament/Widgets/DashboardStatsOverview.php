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
      
    $todaySales = Invoice::whereDate('invoice_date', now()->today())->sum('grand_total') ?? 0;

        $todayGrossProfit = InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('lot_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
            ->whereDate('invoices.invoice_date', now()->today())
            ->sum(DB::raw('(invoice_items.rate - lot_items.cost_price) * invoice_items.qty')) ?? 0;

        $todayDiscounts = Invoice::whereDate('invoice_date', now()->today())->sum('discount') ?? 0;
        $todayProfit = $todayGrossProfit - $todayDiscounts;


        // ==========================================
        // 2. THIS MONTH'S DATA (Is Mahine ka Data)
        // ==========================================
        $monthlySales = Invoice::whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->sum('grand_total') ?? 0;

        $monthlyGrossProfit = InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('lot_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
            ->whereMonth('invoices.invoice_date', now()->month)
            ->whereYear('invoices.invoice_date', now()->year)
            ->sum(DB::raw('(invoice_items.rate - lot_items.cost_price) * invoice_items.qty')) ?? 0;

        $monthlyDiscounts = Invoice::whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->sum('discount') ?? 0;

        $monthlyProfit = $monthlyGrossProfit - $monthlyDiscounts;


        // ==========================================
        // 3. OVERALL CURRENT BALANCES (Point-in-Time)
        // ==========================================
        // Customer dues aage peeche ki date ke bajaye hamesha CURRENT total hotay hain
        $receivables = Customer::sum('current_balance') ?? 0;

        // Current Inventory Worth
        $stockValue = LotItem::sum(DB::raw('qty_available * cost_price')) ?? 0;

        $todayNewUdhaar = Invoice::whereDate('invoice_date', now()->today())
            ->sum(DB::raw('grand_total - amount_paid')) ?? 0;

        $expectedStockProfit = LotItem::sum(DB::raw('qty_available * (retail_price - cost_price)')) ?? 0;
        // ==========================================
        // CARDS DISPLAY
        // ==========================================
        return [
            Stat::make("Today's Sales", 'Rs ' . number_format($todaySales, 0))
                ->description('Profit: Rs ' . number_format($todayProfit, 0))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('This Month Sales', 'Rs ' . number_format($monthlySales, 0))
                ->description('Profit: Rs ' . number_format($monthlyProfit, 0))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Total Receivables', 'Rs ' . number_format($receivables, 0))
                // ->description('Pending dues from customers')
                ->description("Today's Debt: Rs " . number_format($todayNewUdhaar, 0))
                // ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Stock Value', 'Rs ' . number_format($stockValue, 0))
                // ->description('Current inventory value')
                ->description('Expected Profit: Rs ' . number_format($expectedStockProfit, 0))
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),
        ];
    }
}
