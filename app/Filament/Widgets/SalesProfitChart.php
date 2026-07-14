<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SalesProfitChart extends ChartWidget
{
    protected ?string $heading = 'Sales Profit Chart';

    protected function getData(): array
    {
        $data = Trend::model(Invoice::class)
            ->between(
                start: now()->subMonths(6),
                end: now(),
            )
            ->perMonth()
            ->sum('grand_total');
 
        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#10b981', // Green line
                    'fill' => false,
                ],
                // Dusra dataset profit ka add kar sakte hain
                [
                    'label' => 'Profit',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate * 0.25), // Example: 25% margin assume kiya hai
                    'borderColor' => '#064e3b', // Dark green line
                    'fill' => false,
                ]
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
