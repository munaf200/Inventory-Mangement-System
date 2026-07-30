<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
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
                'signature'    => 'Haroon',
                'account_name' => 'Haroon and Sons',
                'bank'         => 'Borcele Bank',
                'account_no'   => '0123 4567 8901',
            ],
        ])->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "invoice-{$record->invoice_number}.pdf"
        );
    })
        ];
    }
}
