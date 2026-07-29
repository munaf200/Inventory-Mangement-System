<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use App\Models\Purchase;
use App\Models\SupplierLedger;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
// use Filament\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('voucher_number')
                    ->label('Payment Voucher #')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->default(fn() => 'SPV-' . date('Ymd') . '-' . rand(1000, 9999)) // Auto generate like SPV-20260713-1234
                    ->columnSpan(1),

                DatePicker::make('payment_date')
                    ->label('Payment Date')
                    ->default(now())
                    ->required()
                    ->columnSpan(1),

                TextInput::make('amount_paid')
                    ->label('Amount Paid')
                    ->numeric()
                    ->required()
                    ->prefix('Rs.')
                    ->columnSpan(1),

                Select::make('payment_mode')
                    ->label('Payment Mode')
                    ->options([
                        'cash' => 'Cash',
                        'bank transfer' => 'Bank Transfer',
                        'cheque' => 'Cheque',
                    ])
                    ->default('cash')
                    ->required()
                    ->live() // Live isliye taake mode change hone par agla field dikh sake
                    ->columnSpan(1),

                TextInput::make('reference_no')
                    ->label('Cheque / Trans ID (Optional)')
                    ->helperText('Agar bank ya cheque se payment ki hai toh yahan number likhein')
                    // Yeh field sirf tab dikhega jab mode bank ya cheque ho
                    ->visible(fn (Get $get) => in_array($get('payment_mode'), ['bank transfer', 'cheque']))
                    ->columnSpan(2),

                Textarea::make('notes')
                    ->label('Notes / Remarks')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('voucher_number')
            ->columns([
                TextColumn::make('voucher_number')
                    ->label('Voucher #')
                    ->searchable()
                    ->weight('bold'),
                 TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('d-M-Y')
                    ->sortable(),
                TextColumn::make('amount_paid')
                    ->label('Amount Paid')
                    ->money('PKR')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('payment_mode')
                    ->label('Payment Mode')
                    ->badge(),
                TextColumn::make('reference_no')
                    ->label('Ref / Cheque #'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                AssociateAction::make(),
             CreateAction::make()
    ->label('New supplier payment')
    ->modalHeading('Create Supplier Payment')
    ->using(function (array $data, string $model): Model {
        return DB::transaction(function () use ($data, $model) {
            
            $supplierId = $data['supplier_id'] ?? $this->getOwnerRecord()->id;
            $data['supplier_id'] = $supplierId; 
            
            // 1. Payment Create (SupplierPaymentObserver is ki apni Ledger entry banayega)
            $payment = $model::create($data);
            $paymentAmount = floatval($data['amount_paid']);

            // 2. FIFO Logic - Purani Purchases ko clear karna
            $remainingPayment = $paymentAmount;

            $pendingPurchases = Purchase::where('supplier_id', $supplierId)
                ->where('balance_amount', '>', 0)
                ->orderBy('purchase_date', 'asc')
                ->get();

            foreach ($pendingPurchases as $purchase) {
                if ($remainingPayment <= 0) {
                    break;
                }

                $allocateAmount = min($purchase->balance_amount, $remainingPayment);

                // Allocation Record Create Karein
                SupplierPaymentAllocation::create([
                    'supplier_payment_id' => $payment->id,
                    'purchase_id'         => $purchase->id,
                    'amount'              => $allocateAmount,
                ]);

                // Naya Unpaid Balance Aur Status Calculate Karein
                $newBalance = $purchase->balance_amount - $allocateAmount;
                $status = ($newBalance <= 0) ? 'paid' : 'partial';

                // FIX: 
                // 1. amount_paid ko change nahi kia (wo original spot payment hi rahegi)
                // 2. updateQuietly use kia taake PurchaseObserver dobara na chale
                $purchase->updateQuietly([
                    'balance_amount' => $newBalance,
                    'status'         => $status,
                ]);

                $remainingPayment -= $allocateAmount;
            }

            return $payment;
        });
    })
    ->successNotificationTitle('Payment created & allocated successfully!'),
     ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
