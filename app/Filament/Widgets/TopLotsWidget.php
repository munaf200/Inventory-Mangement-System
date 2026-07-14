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

        $labels = [];
        $profitData = [];

        if ($lotId !== 'all') {
            // ==========================================
            // CASE 1: AGAR SPECIFIC LOT SELECT HAI (SHOW ITEMS)
            // ==========================================
            $itemsQuery = DB::table('lot_items')
                ->join('invoice_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->where('lot_items.purchase_id', $lotId)
                ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
                ->select(
                    'lot_items.id',
                    // NOTE: Agar aapke lot_items table mein column ka naam "item_name" ya "product_name" hai, 
                    // to niche "lot_items.name" ko us se badal dijiyega.
                    'lot_items.item as item_label', 
                    DB::raw('SUM(invoice_items.qty * invoice_items.rate) - SUM(invoice_items.qty * lot_items.cost_price) as profit_generated')
                )
                ->groupBy('lot_items.id', 'lot_items.item')
                ->orderBy('profit_generated', 'desc')
                ->limit(5)
                ->get();

            $labels = $itemsQuery->pluck('item_label')->toArray();
            $profitData = $itemsQuery->pluck('profit_generated')->map(fn ($val) => (float) $val)->toArray();

        } else {
            // ==========================================
            // CASE 2: AGAR "ALL LOTS" SELECT HAI (SHOW LOTS)
            // ==========================================
            $lotsQuery = Purchase::query()
                ->select('purchases.id', 'purchases.lot_number', DB::raw('
                    SUM(invoice_items.qty * invoice_items.rate) - SUM(invoice_items.qty * lot_items.cost_price) as profit_generated
                '))
                ->join('lot_items', 'lot_items.purchase_id', '=', 'purchases.id')
                ->join('invoice_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
                ->groupBy('purchases.id', 'purchases.lot_number')
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