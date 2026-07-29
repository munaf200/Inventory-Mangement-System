<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase;
use Livewire\Attributes\On;

class TopLotsWidget extends ChartWidget
{
    // Dashboard par automatic show hone se rokne ke liye
    protected static bool $isDiscovered = false;

    // Grid mein half width (1 out of 2 columns) lene ke liye
    protected int | string | array $columnSpan = 1;

    public array $filters = [];

    /**
     * Chart ka Heading dynamic karne ke liye function
     */
    public function getHeading(): string
    {
        $lotId = $this->filters['purchase_lot_id'] ?? 'all';

        // Agar specific lot select hai to Heading change ho jaye
        return $lotId !== 'all' 
            ? 'Top 5 Profit-Making Items of Selected LOT' 
            : 'Top 5 Profit-Making LOTS';
    }

    /**
     * Parent page se dynamic filter event catch karne ke liye
     */
    #[On('update-analytics-filters')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * Database se data fetch karne ka dynamic logic
     */
    protected function getData(): array
    {
       $fromDate = $this->filters['from_date'] ?? now()->subDays(30)->format('Y-m-d');
        $toDate = $this->filters['to_date'] ?? now()->format('Y-m-d');
        $lotId = $this->filters['purchase_lot_id'] ?? 'all';

        // FIX 1: Date Range me poora din (Start & End Time) include kia
        $start = $fromDate . ' 00:00:00';
        $end = $toDate . ' 23:59:59';

        $labels = [];
        $profitData = [];

        if ($lotId !== 'all') {
            // ==========================================
            // CASE 1: SPECIFIC LOT SELECTED (SHOW ITEMS)
            // ==========================================
            $itemsQuery = DB::table('lot_items')
                ->join('invoice_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereNull('invoices.deleted_at')   // FIX 2: Soft deleted invoices ko ignore karna
                ->whereNull('lot_items.deleted_at')  // Soft deleted items ko ignore karna
                ->where('lot_items.purchase_id', $lotId)
                ->whereBetween('invoices.invoice_date', [$start, $end])
                ->select(
                    'lot_items.id',
                    'lot_items.item as item_label', 
                    DB::raw('SUM(invoice_items.qty * invoice_items.rate) - SUM(invoice_items.qty * lot_items.cost_price) as profit_generated')
                )
                ->groupBy('lot_items.id', 'lot_items.item')
                ->having('profit_generated', '>', 0) // FIX 3: Pie Chart ke liye sirf positive profit wale items
                ->orderBy('profit_generated', 'desc')
                ->limit(5)
                ->get();

            $labels = $itemsQuery->pluck('item_label')->toArray();
            $profitData = $itemsQuery->pluck('profit_generated')->map(fn ($val) => (float) $val)->toArray();

        } else {
            // ==========================================
            // CASE 2: "ALL LOTS" SELECTED (SHOW LOTS)
            // ==========================================
            $lotsQuery = Purchase::query()
                ->select('purchases.id', 'purchases.lot_number', DB::raw('
                    SUM(invoice_items.qty * invoice_items.rate) - SUM(invoice_items.qty * lot_items.cost_price) as profit_generated
                '))
                ->join('lot_items', 'lot_items.purchase_id', '=', 'purchases.id')
                ->join('invoice_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereNull('invoices.deleted_at')   // FIX 2: Soft deleted records ignore
                ->whereNull('lot_items.deleted_at')
                ->whereNull('purchases.deleted_at')
                ->whereBetween('invoices.invoice_date', [$start, $end])
                ->groupBy('purchases.id', 'purchases.lot_number')
                ->having('profit_generated', '>', 0) // FIX 3: Positive Profit Filter
                ->orderBy('profit_generated', 'desc')
                ->limit(5)
                ->get();

            $labels = $lotsQuery->pluck('lot_number')->toArray();
            $profitData = $lotsQuery->pluck('profit_generated')->map(fn ($val) => (float) $val)->toArray();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Profit Generated (PKR)',
                    'data' => $profitData,
                    'backgroundColor' => [
                        '#10B981', // Emerald
                        '#3B82F6', // Blue
                        '#F59E0B', // Amber
                        '#EC4899', // Pink
                        '#8B5CF6', // Purple
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}