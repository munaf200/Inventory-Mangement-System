<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Purchase;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lot Details')
                    ->description('basic information of the supplier')
                    ->schema([
                        TextInput::make('lot_number')
                            ->label('Lot Number')
                            ->default(function () {
                                $lastRecord = Purchase::latest()->first();
                                $nextId = $lastRecord ? $lastRecord->id + 1 : 1;

                                return 'LOT-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
                            })
                            ->readOnly()
                            ->dehydrated()
                            ->required(),

                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->label('Supplier')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        DatePicker::make('purchase_date')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),



                        Textarea::make('notes')
                            ->label('Extra Notes')
                            ->columnSpanFull(),
                    ])->columns(3)->columnSpanFull(),

                // 👕 SECTION 2: Lot ke andar aane wale items (Repeater)
                Section::make('Lot Items')
                    ->schema([
                        Repeater::make('lotItems')
                            ->relationship()
                            ->live()
                            // ->afterStateUpdated(function (Get $get, Set $set) {
                            //     $items = $get('lotItems') ?? [];

                            //     // Har item ki 'qty_purchased' ko jama (sum) karega
                            //     $totalQuantity = collect($items)->sum(function ($item) {
                            //         return intval($item['qty_purchased'] ?? 0);
                            //     });

                            //     $set('total_lot_item_quantity', $totalQuantity);
                            // })
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $items = $get('lotItems') ?? [];

                                $totalQuantity = 0;
                                $totalLotPrice = 0;

                                foreach ($items as $item) {
                                    $qty = intval($item['qty_purchased'] ?? 0);
                                    $cost = floatval($item['cost_price'] ?? 0);

                                    $totalQuantity += $qty;
                                    // Total Lot Price = Har item ki (Quantity * Cost Price)
                                    $totalLotPrice += ($qty * $cost);
                                }

                                // Quantity update karega
                                $set('total_lot_item_quantity', $totalQuantity);

                                // Lot Price update karega
                                $set('lot_price', $totalLotPrice);

                                // Balance update karega
                                $paid = floatval($get('amount_paid') ?? 0);
                                $set('balance_amount', $totalLotPrice - $paid);
                            })
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
                                    // ->afterStateUpdated(function ($state, Set $set, Get $get) {

                                    //     $set('qty_available', $state);

                                    //     $items = $get('../../lotItems') ?? [];
                                    //     $totalQuantity = collect($items)->sum(function ($item) {
                                    //         return intval($item['qty_purchased'] ?? 0);
                                    //     });

                                    //     $set('../../total_lot_item_quantity', $totalQuantity);
                                    // })
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $set('qty_available', $state);

                                        $items = $get('../../lotItems') ?? [];
                                        $totalQuantity = 0;
                                        $totalLotPrice = 0;

                                        foreach ($items as $item) {
                                            $qty = intval($item['qty_purchased'] ?? 0);
                                            $cost = floatval($item['cost_price'] ?? 0);

                                            $totalQuantity += $qty;
                                            $totalLotPrice += ($qty * $cost);
                                        }

                                        $set('../../total_lot_item_quantity', $totalQuantity);
                                        $set('../../lot_price', $totalLotPrice);

                                        $paid = floatval($get('../../amount_paid') ?? 0);
                                        $set('../../balance_amount', $totalLotPrice - $paid);
                                    })
                                    ->columnSpan(1),

                                Hidden::make('qty_available'), // User ko dikhane ki zaroorat nahi

                                // TextInput::make('cost_price')
                                //     ->label('Cost Price')
                                //     ->numeric()
                                //     ->required()
                                //     ->prefix('Rs.')
                                //     ->columnSpan(1),
                                TextInput::make('cost_price')
                                    ->label('Cost Price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rs.')
                                    ->columnSpan(1)
                                    ->live(onBlur: true) // NAYA CODE YAHAN SE SHURU HAI
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $items = $get('../../lotItems') ?? [];

                                        $totalLotPrice = 0;
                                        foreach ($items as $item) {
                                            $qty = intval($item['qty_purchased'] ?? 0);
                                            $cost = floatval($item['cost_price'] ?? 0);
                                            $totalLotPrice += ($qty * $cost);
                                        }

                                        $set('../../lot_price', $totalLotPrice);

                                        $paid = floatval($get('../../amount_paid') ?? 0);
                                        $set('../../balance_amount', $totalLotPrice - $paid);
                                    }),

                                TextInput::make('retail_price')
                                    ->label('Retail Price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rs.')
                                    ->columnSpan(1),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Add New Item')
                    ])->columnSpanFull(),

                Section::make('Payment & Summary')
                    ->description('View and manage the supplier payment and lot summary here.')
                    ->columns(4) // Pure card ko 3 barabar columns me divide karega
                    ->schema([

                        TextInput::make('total_lot_item_quantity') // Placeholder ko hata kar TextInput kar diya
                            ->label('Total Lot Items Quantity')
                            ->numeric() // Ab user isko khud bhi edit kar sakta hai aur ye DB me save hoga
                            ->default(0),

                        TextInput::make('lot_price') // Apni field ka original name check karlein (e.g. total_lot_price)
                            ->label('Lot Price')
                            ->numeric()
                            ->live(onBlur: true) // Jaise hi user input se bahar click kare, calculation chalay
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $total = floatval($get('lot_price') ?? 0);
                                $paid = floatval($get('amount_paid') ?? 0);

                                $set('balance_amount', $total - $paid);
                            }),
                        TextInput::make('amount_paid')
                            ->label('Paid Amount')
                            ->numeric()
                            ->prefix('Rs.')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $total = floatval($get('lot_price') ?? 0);
                                $paid = floatval($get('amount_paid') ?? 0);

                                // Remaining Balance ko real-time update karega
                                $set('balance_amount', $total - $paid);
                            }),

                        // FIELD 3: Remaining Balance (Auto-calculated aur Read-only)
                        TextInput::make('balance_amount')
                            ->label('Remaining Balance')
                            ->numeric()
                            ->prefix('Rs.')
                            ->readonly()
                            ->disabled()
                            ->dehydrated() // Database mein value save karwane ke liye
                            ->extraAttributes(function (Get $get) {
                                // Agar udhaar baki hai to text RED ho jaye, warna GREEN
                                $total = floatval($get('total_lot_price') ?? 0);
                                $paid = floatval($get('amount_paid') ?? 0);
                                $isDue = ($total - $paid) > 0;

                                return [
                                    'style' => $isDue ? 'color: #ef4444; font-weight: bold;' : 'color: #22c55e; font-weight: bold;'
                                ];
                            }),
                    ])->columnSpanFull()
            ]);
    }
}
