<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

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
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
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
         ->recordTitleAttribute('voucher_no')
            ->defaultSort('transaction_date', 'asc') // Chronological order (Purani dates pehle)
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('d-M-Y')
                    ->sortable(),

                TextColumn::make('voucher_no')
                    ->label('Voucher #')
                    ->searchable()
                    ->placeholder('-')
                    ->copyable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'opening_balance' => 'gray',
                        'purchase'        => 'danger',  // Red: Bill / Dena Barha
                        'payment'         => 'success', // Green: Payment / Dena Kam Hua
                        'purchase_return' => 'warning',
                        default           => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'opening_balance' => 'Opening Bal',
                        'purchase'        => 'Purchase',
                        'payment'         => 'Payment',
                        'purchase_return' => 'Return',
                        default           => ucfirst($state),
                    }),

                TextColumn::make('debit')
                    ->label('Debit (Payment Sent -)')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('PKR ')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('credit')
                    ->label('Credit (Invoice Bill +)')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('PKR ')
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Balance (Payable)')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('PKR ')
                    ->weight(FontWeight::Bold)
                    ->sortable(),
            ])
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
