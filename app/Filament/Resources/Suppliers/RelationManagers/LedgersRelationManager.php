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
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('description')
                    ->label('Description')
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'opening_balance' => 'warning',
                        'purchase' => 'danger', // Udhaar barha (Debit)
                        'payment' => 'success', // Udhaar kam hua (Credit)
                    }),
                TextColumn::make('debit')
                    ->label('Debit (Humne Dene Hain)')
                    ->money('PKR')
                    ->color('danger'),
                TextColumn::make('credit')
                    ->label('Credit (Hum De Chuke)')
                    ->money('PKR')
                    ->color('success'),
                TextColumn::make('balance')
                    ->label('Balance (Baqaya)')
                    ->money('PKR')
                    ->weight('bold'),
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
