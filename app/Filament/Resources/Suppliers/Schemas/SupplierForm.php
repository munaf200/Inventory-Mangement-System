<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Actions\Action;
// use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Hidden;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Supplier Information')
                    ->description('Enter the basic information of the factory or warehouse owner.')
                    ->schema([
                        Hidden::make('is_editing')
                            ->default(false)
                            ->live()
                            ->dehydrated(false),
                        TextInput::make('name')
                            ->label('Supplier / Factory Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1)
                            ->suffixAction(
                                Action::make('toggleEdit')
                                    ->icon('heroicon-m-pencil-square')
                                    ->action(function (Get $get, Set $set) {
                                        // Pencil pe click par true/false switch hoga
                                        $set('is_editing', ! $get('is_editing'));
                                    })
                            ),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(255)
                            ->columnSpan(1),
                         // 1. Placeholder ka naam 'current_balance_display' kar diya
                        Placeholder::make('current_balance_display')
                            ->label("Current Balance")
                            ->visible(fn($record) => $record !== null)
                            ->content(function ($record) {
                                $balance = (float) $record->current_balance;

                                if ($balance > 0) {
                                    return new HtmlString(
                                        '<span style="color: #ef4444; font-size: 1.5rem; font-weight: 800;">Rs. ' . number_format($balance, 2) . '</span>'
                                    );
                                } else {
                                    return new HtmlString(
                                        '<span style="color: #10b981; font-size: 1.5rem; font-weight: 800;">Rs. ' . number_format($balance, 2) . '</span>'
                                    );
                                }
                            }),

                             Textarea::make('address')
                            ->label('Factory Address')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ,
                       TextInput::make('opening_balance')
                            ->label('Opening Balance')
                            ->numeric()
                            ->default(0.00)
                            ->prefix('Rs.')
                            ->visible(fn(Get $get) => $get('is_editing'))
                            // ->helperText('Agar aapne pehle se inke paise dene hain toh yahan likhein.')
                            ->columnSpanFull()
                            ->live(onBlur: true) // Type karne ke baad trigger kare ga (performance ke liye behtar hai)
                            ->afterStateUpdated(function ($state, Set $set) {
                                // 2. Yahan direct 'current_balance' ko update karein
                                $set('current_balance', $state);
                            }),

                        // 3. Hidden field ka naam direct database column wala rakha hai
                        Hidden::make('current_balance')
                            ->default(0),



                       

                        Textarea::make('notes')
                            ->label('Extra Notes / Remarks')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->visible(fn(Get $get) => $get('is_editing')),
                    ])->columns(3)->columnSpanFull(),
            ]);
    }
}
