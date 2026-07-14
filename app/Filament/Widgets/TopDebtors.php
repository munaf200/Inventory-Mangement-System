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
            ->query(
                Customer::query()->orderBy('opening_balance', 'desc')->limit(5))
            ->columns([
                TextColumn::make('name')
                    ->label('Customer Name'),
                TextColumn::make('phone')
                    ->label('Phone'),
                TextColumn::make('opening_balance')
                    ->label('Dues Amount')
                    ->money('PKR', true) // currency format agar PKR hai
                    ->color('danger')
                    ->weight('bold'),
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
