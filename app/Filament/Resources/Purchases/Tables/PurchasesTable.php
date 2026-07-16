<?php

namespace App\Filament\Resources\Purchases\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lot_number')
                    ->label('Lot #')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('purchase_date')
                    ->date('d-M-Y')
                    ->sortable(),
                    
                TextColumn::make('total_lot_item_quantity') // Kitne items aye is lot mein
                    ->counts('lotItems')
                    ->label('Total Items')
                    ->badge(),

                TextColumn::make('lot_price')
                    ->label('Total Amount')
                    ->numeric(2)
                    ->money('PKR')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
