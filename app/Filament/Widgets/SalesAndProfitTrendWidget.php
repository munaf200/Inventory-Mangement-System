<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class SalesAndProfitTrendWidget extends ChartWidget
{
    // Auto-discover off taake main dashboard par na jaye
    protected static bool $isDiscovered = false;

    // Grid mein half width (1 out of 2 columns) lene ke liye
    protected int | string | array $columnSpan = 1;

    public array $filters = [];

    /**
     * Dropdown selection ke mutabiq heading dynamic karna
     */
    public function getHeading(): string
    {
        $lotId = $this->filters['purchase_lot_id'] ?? 'all';

        return $lotId !== 'all' 
            ? 'Sales & Profit Trend of Selected LOT' 
            : 'Overall Sales & Profit Trend';
    }

    /**
     * Parent filter change hone par event capture karna
     */
    #[On('update-analytics-filters')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * Query jo Sales aur Profit ko date-wise grouping ke sath nikalegi
     */
    protected function getData(): array
    {
      $fromDate = $this->filters['from_date'] ?? now()->subDays(30)->format('Y-m-d');
        $toDate = $this->filters['to_date'] ?? now()->format('Y-m-d');
        $lotId = $this->filters['purchase_lot_id'] ?? 'all';

        // FIX 1: Full Day Range set ki hai (Start & End Time)
        $start = $fromDate . ' 00:00:00';
        $end = $toDate . ' 23:59:59';

        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('lot_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
            ->whereNull('invoices.deleted_at')   // FIX 2: Soft deleted invoices filter kiye
            ->whereNull('lot_items.deleted_at')  // Soft deleted lot items filter kiye
            ->whereBetween('invoices.invoice_date', [$start, $end]);

        if ($lotId !== 'all') {
            $query->where('lot_items.purchase_id', $lotId);
        }

        // FIX 3: DATE() function apply kia taake exact daily wise grouping bane
        $trendData = $query->select(
            DB::raw('DATE(invoices.invoice_date) as formatted_date'),
            DB::raw('SUM(invoice_items.qty * invoice_items.rate) as total_sales'),
            DB::raw('SUM(invoice_items.qty * invoice_items.rate) - SUM(invoice_items.qty * lot_items.cost_price) as net_profit')
        )
        ->groupBy(DB::raw('DATE(invoices.invoice_date)'))
        ->orderBy(DB::raw('DATE(invoices.invoice_date)'), 'asc')
        ->get();

        $labels = $trendData->pluck('formatted_date')->toArray();
        $sales = $trendData->pluck('total_sales')->map(fn ($val) => (float) $val)->toArray();
        $profit = $trendData->pluck('net_profit')->map(fn ($val) => (float) $val)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales (PKR)',
                    'data' => $sales,
                    'borderColor' => '#3B82F6', 
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 
                    'fill' => true,
                ],
                [
                    'label' => 'Net Profit (PKR)',
                    'data' => $profit,
                    'borderColor' => '#10B981', 
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)', 
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Chart type ko line set kiya
    }
}