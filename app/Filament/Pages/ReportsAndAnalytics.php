<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ReceivablesAgingWidget;
use App\Filament\Widgets\ReportsStatsWidget;
use App\Filament\Widgets\SalesAndProfitTrendWidget;
use App\Filament\Widgets\TopLotsWidget;
use App\Models\Purchase;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ReportsAndAnalytics extends Page implements HasForms
{
    use InteractsWithForms;
    protected string $view = 'filament.pages.reports-and-analytics';

    // protected string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;
    protected static string|UnitEnum|null $navigationGroup = 'Sales & Inventory';

    protected static ?int $navigationSort= 3;

    public ?array $filterData = [];

    public function mount(): void
    {
        $this->form->fill([
            'from_date' => now()->subDays(30)->format('Y-m-d'),
            'to_date' => now()->format('Y-m-d'),
            'purchase_lot_id' => 'all',
        ]);
    }
    // public function form(Form $form): Schema
    // {
    //     return $form
    //         ->schema([
      public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    DatePicker::make('from_date')
                        ->label('Filter from Date')
                        ->native(false)
                        ->required(),
                    
                    DatePicker::make('to_date')
                        ->label('to Date')
                        ->native(false)
                        ->required(),

                    Select::make('purchase_lot_id')
                        ->label('Filter by Lots')
                        ->options([
                            'all' => 'ALL Lots',
                        ] + Purchase::pluck('lot_number', 'id')->toArray())
                        ->selectablePlaceholder(false),

                   // Inline Action Button
                
                   // Button is aligned with input height
                    Actions::make([
                        Action::make('submit')
                            ->label('Apply Filters')
                            ->icon('heroicon-m-funnel')
                            ->color('warning')
                            ->action('submitFilters'),
                    ])
                    ->extraAttributes(['style' => 'margin-top: 26px; width: 100%']),
                   // ]),
                ])->columns(4),
            ])
            ->statePath('filterData');
    }

    public function submitFilters(): void
    {
        $data = $this->form->getState();
        
        // Dispatch event to all widgets safely
        $this->dispatch('update-analytics-filters', filters: $data);
    }

    // Native way to load widgets below the page content
    protected function getFooterWidgets(): array
    {
        return [
            ReportsStatsWidget::class,
            SalesAndProfitTrendWidget::class,
            TopLotsWidget::class,
            ReceivablesAgingWidget::class,
        ];
    }

    // Grid layout for footer widgets (2 columns total)
    public function getFooterWidgetsColumns(): int  | array
    {
        return 2;
    }
  
}
