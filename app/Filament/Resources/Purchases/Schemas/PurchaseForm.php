<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lot Details')
                    ->description('Supplier aur bill ki bunyadi malomat')
                    ->schema([
                        TextInput::make('lot_number')
                            ->label('Lot / Bill Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                            
                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->label('Supplier (Factory)')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                            
                        DatePicker::make('purchase_date')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('lot_price')
                            ->label('Total Lot Price (Bill Amount)')
                            ->numeric()
                            ->required()
                            ->prefix('Rs.')
                            ->columnSpan(1),

                        Textarea::make('notes')
                            ->label('Extra Notes')
                            ->columnSpanFull(),
                    ])->columns(4)->columnSpanFull(),

                // 👕 SECTION 2: Lot ke andar aane wale items (Repeater)
                Section::make('Lot Items (Maal ki tafseel)')
                    ->schema([
                        Repeater::make('lotItems') // Yeh relationship function ka naam hai model mein
                            ->relationship()
                            ->schema([
                                TextInput::make('item')
                                    ->label('Item Name')
                                    ->required()
                                    ->columnSpan(2),
                                    
                                TextInput::make('brand')
                                    ->label('Brand')
                                    ->columnSpan(1),
                                    
                                TextInput::make('qty_purchased')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->live(onBlur: true)
                                    // Jaise hi Qty Purchased likhi jaye, Qty Available bhi same ho jaye
                                    ->afterStateUpdated(fn ($state, Set $set) => $set('qty_available', $state))
                                    ->columnSpan(1),
                                    
                                Hidden::make('qty_available'), // User ko dikhane ki zaroorat nahi
                                
                                TextInput::make('cost_price')
                                    ->label('Cost Price (Kharid)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rs.')
                                    ->columnSpan(1),
                                    
                                TextInput::make('retail_price')
                                    ->label('Retail Price (Baich)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rs.')
                                    ->columnSpan(1),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Add New Item')
                    ])->columnSpanFull(),
            ]);
    }
}
