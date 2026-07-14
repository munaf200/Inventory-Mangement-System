<?php

namespace App\Filament\Widgets;

use App\Models\LotItem;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LowStockAlerts extends TableWidget
{
    protected static ?string $heading = 'Alerts (Low Stock)';
    protected static ?int $sort = 3;
    public function table(Table $table): Table
    {
        return $table
            ->query(LotItem::query()->where('qty_available', '<=', 50)->orderBy('qty_available', 'asc'))
            ->columns([
                TextColumn::make('item')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('purchase_lot_id') // Ya lot number
                    ->label('Lot No.'),
                TextColumn::make('qty_available')
                    ->label('Qty Left')
                    ->badge()
                    ->color('danger'),
            ])
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
