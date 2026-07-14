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

        // Core Query: invoice items ko join kar ke sales aur profit calculate karna
        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('lot_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate]);

        // AGAR SPECIFIC LOT SELECT HAI TO FILTER LAGANA
        if ($lotId !== 'all') {
            $query->where('lot_items.purchase_id', $lotId);
        }

        // Grouping data daily wise (kis din kitni sale aur profit hui)
        $trendData = $query->select(
            'invoices.invoice_date',
            DB::raw('SUM(invoice_items.qty * invoice_items.rate) as total_sales'),
            DB::raw('SUM(invoice_items.qty * invoice_items.rate) - SUM(invoice_items.qty * lot_items.cost_price) as net_profit')
        )
        ->groupBy('invoices.invoice_date')
        ->orderBy('invoices.invoice_date', 'asc')
        ->get();

        // Arrays prepare karna Chart JS ke liye
        $labels = $trendData->pluck('invoice_date')->toArray();
        $sales = $trendData->pluck('total_sales')->map(fn ($val) => (float) $val)->toArray();
        $profit = $trendData->pluck('net_profit')->map(fn ($val) => (float) $val)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales (PKR)',
                    'data' => $sales,
                    'borderColor' => '#3B82F6', // Modern Blue color
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)', // Light blue shade under line
                    'fill' => true,
                ],
                [
                    'label' => 'Net Profit (PKR)',
                    'data' => $profit,
                    'borderColor' => '#10B981', // Emerald/Green color
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)', // Light green shade under line
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