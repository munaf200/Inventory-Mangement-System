<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecentInvoices extends TableWidget
{
    protected static ?string $heading = 'Last 5 Invoices';
    
    // Dashboard par iski position set karne ke liye
    protected static ?int $sort = 5; 

    // Agar table ko full width dikhana ho (optional)
    // protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(Invoice::query()->latest()->limit(5))
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    // ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Customer relation ke through naam dikhana (Ensure Invoice model mein customer() ka relation bana ho)
                TextColumn::make('customer.name')
                    ->label('Customer Name'),
                    // ->searchable(),

                // Amount yaani grand total dikhane ke liye
                TextColumn::make('grand_total')
                    ->label('Amount')
                    ->money('PKR', true) // PKR currency format ke liye
                    ->weight('bold')
                    ->color('success'),

                // Status dikhane ke liye (Paid, Unpaid, Partial)
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ]),

               
            ])->paginated(false)
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
