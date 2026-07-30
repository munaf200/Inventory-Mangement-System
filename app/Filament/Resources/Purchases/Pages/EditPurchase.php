<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
            // DeleteAction::make(),
            // ForceDeleteAction::make(),
            // RestoreAction::make(),
        ];
    }
}
