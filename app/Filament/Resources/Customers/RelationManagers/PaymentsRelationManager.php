<?php

namespace App\Filament\Resources\Customers\RelationManagers;

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

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('receipt_number')
                    ->label('Receipt / Voucher #')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->default(fn() => 'CRV-' . date('Ymd') . '-' . rand(1000, 9999)) // Customer Receipt Voucher like CRV-20260713-5432
                    ->columnSpan(1),

                DatePicker::make('payment_date')
                    ->label('Date Received')
                    ->default(now())
                    ->required()
                    ->columnSpan(1),

                TextInput::make('amount_received')
                    ->label('Amount Received')
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
                    ->live()
                    ->columnSpan(1),

                TextInput::make('reference_no')
                    ->label('Cheque No / Online Ref (Optional)')
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
            ->recordTitleAttribute('receipt_number')
            ->columns([
               TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('d-M-Y')
                    ->sortable(),
                
                TextColumn::make('receipt_number')
                    ->label('Receipt #')
                    ->searchable()
                    ->weight('bold'),
                
                TextColumn::make('amount_received')
                    ->label('Amount Received')
                    ->money('PKR')
                    ->sortable()
                    ->color('success'),
                
                TextColumn::make('payment_mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'bank transfer' => 'info',
                        'cheque' => 'warning',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
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
