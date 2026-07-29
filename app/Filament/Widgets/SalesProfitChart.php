<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\DB;

class SalesProfitChart extends ChartWidget
{
    protected ?string $heading = 'Sales Profit Chart';

//   protected static ?string $heading = 'Sales vs Real Profit';

    // Default dropdown option
    public ?string $filter = '6m';

    /**
     * Dropdown Filters List Define Karein
     */
    protected function getFilters(): ?array
    {
        return [
            '1m' => 'Last 30 Days',
            '6m' => 'Last 6 Months',
            '1y' => 'Last 1 Year',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $labels = [];
        $salesData = [];
        $profitData = [];

        if ($activeFilter === '1m') {
            // Pichle 30 Din (Daily Calculation)
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $start = $date->copy()->startOfDay();
                $end = $date->copy()->endOfDay();

                $labels[] = $date->format('d M');

                $this->calculatePeriodData($start, $end, $salesData, $profitData);
            }
        } else {
            // 6 Months ya 1 Year (Monthly Calculation)
            $monthsCount = ($activeFilter === '1y') ? 12 : 6;

            for ($i = $monthsCount - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $start = $date->copy()->startOfMonth();
                $end = $date->copy()->endOfMonth();

                $labels[] = $date->format('M Y');

                $this->calculatePeriodData($start, $end, $salesData, $profitData);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => ' Total Sales',
                    'data' => $salesData,
                    'borderColor' => '#10b981', // Green line
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => ' Net Profit',
                    'data' => $profitData,
                    'borderColor' => '#0284c7', // Blue line
                    'backgroundColor' => 'rgba(2, 132, 199, 0.1)',
                    'fill' => true,
                ]
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Specified Date Range ke liye Sales aur Profit Calculate karne ka Helper Function
     */
    private function calculatePeriodData(Carbon $start, Carbon $end, array &$salesData, array &$profitData): void
    {
        // 1. Total Sales
        $sales = Invoice::whereBetween('created_at', [$start, $end])
            ->sum('grand_total') ?? 0;

        // 2. Gross Profit: (Rate - Cost Price) * Qty
        $grossProfit = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('lot_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
            ->whereNull('invoices.deleted_at')
            ->whereBetween('invoices.created_at', [$start, $end])
            ->sum(DB::raw('(invoice_items.rate - lot_items.cost_price) * invoice_items.qty')) ?? 0;

        // 3. Discounts
        $discounts = Invoice::whereBetween('created_at', [$start, $end])
            ->sum('discount') ?? 0;

        // 4. Net Profit
        $netProfit = $grossProfit - $discounts;

        $salesData[] = round($sales, 2);
        $profitData[] = round($netProfit, 2);
    }

    protected function getType(): string
    {
        return 'line';
    }
}
