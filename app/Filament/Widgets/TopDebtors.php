<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TopDebtors extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            // ->query(
            //     Customer::query()->orderBy('opening_balance', 'desc')->limit(5))
            // ->query(
            //     Customer::query()
            //         ->where('current_balance', '>', 0) // 0 balance wale hide rahenge
            //         ->orderBy('current_balance', 'desc') // Sub se zyada dues wale pehle
            // )
            ->query(
                Customer::query()
                    ->where('current_balance', '>', 0) // Sirf jin par dues hain
                    ->orderBy('current_balance', 'desc')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Customer Name'),
                TextColumn::make('phone')
                    ->label('Phone'),
                TextColumn::make('current_balance')
                    ->label('Dues Amount')
                    ->money('PKR', true) // currency format agar PKR hai
                    ->color('danger')
                    ->weight('bold'),
            ])->defaultPaginationPageOption(5) // First page par sirf 5 rows show honge
            ->paginated([5])
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
