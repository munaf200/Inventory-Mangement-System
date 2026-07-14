<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\LotItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function updateTotals(Get $get, Set $set): void
    {
        // Repeater ke saare items uthao
        $items = $get('items') ?? [];
        $subTotal = 0;

        foreach ($items as $key => $item) {
            $qty = floatval($item['qty'] ?? 0);
            $rate = floatval($item['rate'] ?? 0);
            $rowTotal = $qty * $rate;

            // Har individual item ka total set karo
            $set("items.{$key}.total", $rowTotal);

            $subTotal += $rowTotal;
        }

        // Sub Total set karo
        $set('sub_total', $subTotal);

        // Discount nikal kar Grand Total calculate karo
        $discount = floatval($get('discount') ?? 0);
        $set('grand_total', max(0, $subTotal - $discount));
    }
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Header')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Bill / Invoice Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn() => 'INV-' . strtoupper(uniqid())),

                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->label('Customer (Dukan-dar)')
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('invoice_date')
                            ->default(now())
                            ->required(),

                        Select::make('payment_mode')
                            ->options([
                                'credit' => 'Credit (Udhaar)',
                                'cash' => 'Cash',
                                'bank' => 'Bank Transfer',
                            ])
                            ->default('credit')
                            ->required(),
                    ])->columns(4)->columnSpanFull(),

                // 🛍️ SECTION 2: Items List (Repeater)
                Section::make('Invoice Items (Maal Jo Becha)')
                    ->schema([
                        Repeater::make('items') // Model relation name
                            ->relationship('items')
                            ->schema([
                                Select::make('lot_item_id')
                                    ->label('Select Item (From Lot Stock)')
                                    ->relationship('lotItem', 'item')
                                    ->getOptionLabelFromRecordUsing(fn (LotItem $record) => "{$record->item} [Brand: {$record->brand}] - Available Stock: {$record->qty_available}")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    // ✨ Jadoo: Item select karte hi uski Retail Price database se khud uth aaye
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $lotItem = LotItem::find($state);
                                        if ($lotItem) {
                                            $set('rate', $lotItem->retail_price);
                                            self::updateTotals($get, $set);
                                        }
                                    })
                                    ->columnSpan(3),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set))
                                    ->columnSpan(1),

                                TextInput::make('rate')
                                    ->label('Rate (Price)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rs.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set))
                                    ->columnSpan(1),

                                TextInput::make('total')
                                    ->label('Total')
                                    ->numeric()
                                    ->readonly()
                                    ->prefix('Rs.')
                                    ->columnSpan(1),
                            ])
                            ->columns(6)
                            ->live()
                            ->afterStateHydrated(fn (Get $get, Set $set) => self::updateTotals($get, $set))
                            ->deleteAction(fn (Get $get, Set $set) => self::updateTotals($get, $set))
                            ->addActionLabel('Add Item to Bill'),
                    ])->columnSpanFull(),

                // 💵 SECTION 3: Bill Calculations Summary
                Section::make('Bill Summary')
                    ->schema([
                        TextInput::make('sub_total')
                            ->label('Sub Total')
                            ->numeric()
                            ->readonly()
                            ->prefix('Rs.'),

                        TextInput::make('discount')
                            ->label('Discount Given')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rs.')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),

                        TextInput::make('grand_total')
                            ->label('Grand Total (Net Receivable)')
                            ->numeric()
                            ->readonly()
                            ->prefix('Rs.'),

                        Select::make('status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partial Paid',
                                'paid' => 'Fully Paid',
                            ])
                            ->default('unpaid')
                            ->required(),

                        Textarea::make('notes')
                            ->label('Invoice Description / Notes')
                            ->columnSpanFull(),
                    ])->columns(4)->columnSpanFull(),
            ]);
    }
}
