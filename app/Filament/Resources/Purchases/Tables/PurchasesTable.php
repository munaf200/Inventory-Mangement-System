<?php

namespace App\Filament\Resources\Purchases\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
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
                // TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('downloadPdf')
    ->label('Download PDF')
    ->icon('heroicon-o-arrow-down-tray')
    ->action(function ($record) {
        // Corrected eager loading: Purchase has direct relationship with Supplier & LotItems
        $pdf = Pdf::loadView('pdf.purchase', [
            'record'   => $record->load(['supplier', 'lotItems']),
            'currency' => 'PKR ',
            'company'  => [
                'name'         => 'Haroon and Sons',
                'tagline'      => 'Wholesale Traders',
                'signature'    => 'Zeeshan',
                'account_name' => 'Haroon and Sons',
                'bank'         => 'Meezan Bank',
                'account_no'   => '0123 4567 8901',
            ],
        ])->setPaper('a4');

        // Changed invoice_number to lot_number to match migration
        return response()->streamDownload(
            fn () => print($pdf->output()),
            "purchase-{$record->lot_number}.pdf"
        );
    })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                    // ForceDeleteBulkAction::make(),
                    // RestoreBulkAction::make(),
                ]),
            ]);
    }
}
