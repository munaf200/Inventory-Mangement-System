<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class ReportsStatsWidget extends BaseWidget
{
    // Pehli row poori width legi (2 out of 2 columns)
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    public array $filters = [];

    #[On('update-analytics-filters')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    protected function getStats(): array
    {
        $fromDate = $this->filters['from_date'] ?? now()->subDays(30)->format('Y-m-d');
        $toDate = $this->filters['to_date'] ?? now()->format('Y-m-d');
        $lotId = $this->filters['purchase_lot_id'] ?? 'all';

        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('lot_items', 'invoice_items.lot_item_id', '=', 'lot_items.id')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate]);

        if ($lotId !== 'all') {
            $query->where('lot_items.purchase_id', $lotId);
        }

        $totals = $query->selectRaw('
            SUM(invoice_items.qty * invoice_items.rate) as revenue,
            SUM(invoice_items.qty * lot_items.cost_price) as cogs
        ')->first();

        $revenue = $totals->revenue ?? 0;
        $cogs = $totals->cogs ?? 0;
        $profit = $revenue - $cogs;

        return [
            Stat::make('Total Revenue', 'PKR ' . number_format($revenue, 2))->color('success'),
            Stat::make('Cost of Goods Sold', 'PKR ' . number_format($cogs, 2))->color('gray'),
            Stat::make('Net Gross Profit', 'PKR ' . number_format($profit, 2))->color('emerald'),
        ];
    }
}