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
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
// use Filament\TextColumn;
// use Filament\TextColumn;
use Filament\Tables\Table;

class LedgersRelationManager extends RelationManager
{
    protected static string $relationship = 'ledgers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('d-M-Y')
                    ->sortable(),
                TextColumn::make('voucher_no') // Changed from receipt_number to voucher_no
                    ->label('Voucher #')
                    ->searchable()
                    ->placeholder('-')
                    ->copyable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'opening_balance' => 'gray',
                        'invoice'         => 'danger',  // Red: Bill / Udhaar Barha
                        'payment'         => 'success', // Green: Wasooli / Udhaar Kam Hua
                        default           => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'opening_balance' => 'Opening Bal',
                        'invoice'         => 'Invoice',
                        'payment'         => 'Payment Recv',
                        default           => ucfirst($state),
                    }),

                TextColumn::make('debit')
                    ->label('Debit')
                    ->money('PKR')
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('credit')
                    ->label('Credit')
                    ->money('PKR')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Running Balance')
                    ->money('PKR')
                    ->weight('bold')
                    ->sortable(),
            ])
            ->defaultSort('transaction_date', 'asc')
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                // EditAction::make(),
                // DissociateAction::make(),
                // DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
