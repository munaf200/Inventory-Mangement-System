<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Invoice;
use App\Models\LotItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                            ->readOnly()
                            ->dehydrated()
                            ->default(
                                function () {
                                    $lastRecord = Invoice::latest()->first();
                                    $nextId = $lastRecord ? $lastRecord->id + 1 : 1;

                                    return 'INV-' . date('Y') . '-'  . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                                    // 'INV-' . date('Y') .'-' . strtoupper(uniqid())
                                }
                            ),

                        // Select::make('customer_id')
                        //     ->relationship('customer', 'name')
                        //     ->label('Customer')
                        //     ->searchable()
                        //     ->preload()
                        //     ->required(),
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->label('Customer')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(), // Yeh zaroori hai taake Placeholder update ho sake


                        DatePicker::make('invoice_date')
                            ->default(now())
                            ->required(),


                        Textarea::make('notes')
                            ->label('Invoice Description / Notes')
                            ->columnSpan(2.5),


                        Placeholder::make('customer_current_balance')
                            ->label("Customer's Old Debt")
                            // 1. Yeh line tab tak is placeholder ko chupaye rakhegi jab tak customer select na ho
                            ->visible(fn(Get $get) => filled($get('customer_id')))
                            ->content(function (Get $get) {
                                $customerId = $get('customer_id');

                                $customer = \App\Models\Customer::find($customerId);

                                if ($customer) {
                                    $balance = $customer->current_balance;

                                    if ($balance > 0) {
                                        // Red color, Bold aur Bara text (Font Size: 1.2rem yaani lagbhag 20px)
                                        return new HtmlString(
                                            '<span style="color: #ef4444; font-size: 1.25rem; font-weight: 800;">Rs. ' . number_format($balance, 2) . '</span>'
                                        );
                                    } else {
                                        // Agar udhar nahi hai to Green color me "Clear" dikhayega
                                        return new HtmlString(
                                            '<span style="color: #10b981; font-size: 1.1rem; font-weight: bold;">Account Cleared (0.00)</span>'
                                        );
                                    }
                                }

                                return null;
                            }),

                    ])->columns(3)->columnSpanFull(),

                // 🛍️ SECTION 2: Items List (Repeater)
                Section::make('Invoice Items')
                    ->schema([
                        Repeater::make('items') // Model relation name
                            ->relationship('items')
                            ->schema([
                                // Select::make('lot_item_id')
                                //     ->label('Select Item (From Lot Stock)')
                                //     ->relationship('lotItem', 'item')
                                //     ->getOptionLabelFromRecordUsing(fn(LotItem $record) => "{$record->item} [Brand: {$record->brand}] - Available Stock: {$record->qty_available}")
                                //     ->searchable()
                                //     ->preload()
                                //     ->required()
                                //     ->live()
                                //     // ✨ Jadoo: Item select karte hi uski Retail Price database se khud uth aaye
                                //     ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                //         $lotItem = LotItem::find($state);
                                //         if ($lotItem) {
                                //             $set('rate', $lotItem->retail_price);
                                //             self::updateTotals($get, $set);
                                //         }
                                //     })
                                //     ->columnSpan(3),

                                Select::make('lot_id') // Agar aapki db me column ka naam 'purchase_id' hai to wo likhein
                                    ->label('Lot')
                                    // Yahan Purchase model se lot numbers uthayenge
                                    ->options(\App\Models\Purchase::pluck('lot_number', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live() // Yeh zaroori hai taake Lot select hone par Item wala select update ho
                                    // Jaise hi nayi Lot select ho, purana item clear ho jaye
                                    ->afterStateUpdated(fn(Set $set) => $set('lot_item_id', null))
                                    // Agar yeh database me save nahi karwana to ->dehydrated(false) laga dain
                                    ->dehydrated()
                                    ->columnSpan(1),

                                // 2. Doosra Dropdown: Lot Ke Items Ke Liye
                                Select::make('lot_item_id')
                                    ->label('Select Item')
                                    ->options(function (Get $get) {
                                        $lotId = $get('lot_id');

                                        if (! $lotId) {
                                            return [];
                                        }

                                        return \App\Models\LotItem::where('purchase_id', $lotId)
                                            ->get()
                                            ->mapWithKeys(function ($record) {
                                                // Agar stock 0 hai to text mein (Out of Stock) likha aayega
                                                $stockStatus = $record->qty_available <= 0 ? '❌ (Out of Stock)' : "- Available: {$record->qty_available}";
                                                return [$record->id => "{$record->item} [Brand: {$record->brand}] {$stockStatus}"];
                                            });
                                    })
                                    // ✨ Jadoo 1: Agar Available Stock 0 ya us se kam hai to option ko click nahi karne dega
                                    ->disableOptionWhen(fn($value) => \App\Models\LotItem::find($value)?->qty_available <= 0)
                                    ->disabled(fn(Get $get) => ! filled($get('lot_id')))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $lotItem = \App\Models\LotItem::find($state);
                                        if ($lotItem) {
                                            $set('rate', $lotItem->retail_price);

                                            // Qty ko reset kar de 1 par jab naya item select ho, taake purani limit ka masla na aaye
                                            $set('qty', 1);

                                            // self::updateTotals($get, $set); // Agar error aaye to isko comment hi rakhein
                                        }
                                    })
                                    ->columnSpan(2),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->hint(function (Get $get) {
                                        $itemId = $get('lot_item_id');
                                        if (! $itemId) return null;

                                        $available = \App\Models\LotItem::find($itemId)?->qty_available ?? 0;
                                        return "Max Avail: {$available}";
                                    })
                                    ->hintColor('danger')
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $itemId = $get('lot_item_id');

                                        if ($itemId) {
                                            // Database se check karein ke kitna stock available hai
                                            $available = \App\Models\LotItem::find($itemId)?->qty_available ?? 0;

                                            // ✨ Jadoo: Agar user ne available se zyada quantity daali hai, to usko available ke barabar kar do
                                            if ($state > $available) {
                                                $set('qty', $available); // Field ki value auto-change ho jayegi
                                            }
                                        }

                                        // Phir totals calculate karne wala function chala dein
                                        self::updateTotals($get, $set);
                                    })
                                    ->columnSpan(1),

                                TextInput::make('rate')
                                    ->label('Rate (Price)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rs.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotals($get, $set))
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
                            ->afterStateHydrated(fn(Get $get, Set $set) => self::updateTotals($get, $set))
                            ->deleteAction(fn(Get $get, Set $set) => self::updateTotals($get, $set))
                            ->addActionLabel('Add Item to Bill'),
                    ])->columnSpanFull(),

                // 💵 SECTION 3: Bill Calculations Summary
                Section::make('Bill Summary')
                    ->schema([
                        Select::make('payment_mode')
                            ->options([
                                'credit' => 'Credit',
                                'cash' => 'Cash',
                                'bank' => 'Bank Transfer',
                            ])
                            ->default('credit')
                            ->required()
                            ->live(),
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
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotals($get, $set)),

                        TextInput::make('grand_total')
                            ->label('Grand Total (Net Receivable)')
                            ->numeric()
                            ->readonly()
                            ->prefix('Rs.'),

                        TextInput::make('amount_paid')
                            ->label('Paid Amount')
                            ->numeric()
                            ->prefix('Rs.')
                            ->default(0)
                            ->live(onBlur: true)
                            ->visible(fn(Get $get) => in_array($get('payment_mode'), ['cash', 'bank'])) // Conditional Visibility
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $total = floatval($get('lot_price') ?? 0);
                                $paid = floatval($get('amount_paid') ?? 0);

                                // Remaining Balance ko real-time update karega
                                $set('balance_amount', $total - $paid);
                            })
                            ->required(fn(Get $get) => $get('payment_mode') === 'cash' || $get('payment_mode') === 'bank'),
                        // ->required(fn(Get $get) => $get('payment_mode') === 'bank'),

                        TextInput::make('ref_no')
                            ->label('Reference Number')
                            ->visible(fn(Get $get) => $get('payment_mode') === 'bank') // Conditional Visibility
                        // ->required(fn(Get $get) => $get('payment_mode') === 'bank'),
                        // Select::make('status')
                        //     ->options([
                        //         'unpaid' => 'Unpaid',
                        //         'partial' => 'Partial Paid',
                        //         'paid' => 'Fully Paid',
                        //     ])
                        //     ->default('unpaid')
                        //     ->required(),


                    ])->columns(3)->columnSpanFull(),
            ]);
    }
}
