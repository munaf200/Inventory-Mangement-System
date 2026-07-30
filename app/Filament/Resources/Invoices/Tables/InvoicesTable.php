<?php

namespace App\Filament\Resources\Invoices\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date('d-M-Y')
                    ->sortable(),

                TextColumn::make('payment_mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'bank' => 'info',
                        'credit' => 'warning',
                    }),

                TextColumn::make('grand_total')
                    ->label('Total Amount')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('downloadPdf')
    ->label('Download PDF')
    ->icon('heroicon-o-arrow-down-tray')
    ->action(function ($record) {
        $pdf = Pdf::loadView('pdf.invoice', [
            'record'   => $record->load(['customer', 'items.lotItem', // lot_items table
            'items.lot']),
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

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "invoice-{$record->invoice_number}.pdf"
        );
    })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    
                    // DeleteBulkAction::make(),
                    
                    
                ]),
            ]);
    }
}
