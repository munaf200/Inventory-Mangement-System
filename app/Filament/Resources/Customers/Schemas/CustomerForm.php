<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->description('Enter the basic information of the customer.')
                    ->schema([
                        // 1. Hidden field jo batayegi edit karna hai ya nahi
                        Hidden::make('is_editing')
                            ->default(false)
                            ->live()
                            ->visible(fn (string $operation, Get $get) => $operation === 'create' || $get('is_editing'))
                            ->dehydrated(false),


                        // TextInput::make('name')
                        //     ->label('Customer / Shop Name')
                        //     ->required()
                        //     ->maxLength(255)
                        //     ->columnSpan(1),
                        // TextInput::make('name')
                        //     ->label('Customer / Shop Name')
                        //     ->required()
                        //     ->suffixAction(
                        //         Action::make('toggleEdit')
                        //             ->icon('heroicon-m-pencil-square')
                        //             ->action(function (Get $get, Set $set) {
                        //                 // Pencil pe click par true/false switch hoga
                        //                 $set('is_editing', ! $get('is_editing'));
                        //             })
                        //     ),
                        TextInput::make('name')
    ->label('Customer / Shop Name')
    ->required()
    ->suffixAction(
        Action::make('toggleEdit')
            ->icon('heroicon-m-pencil-square')
            ->visible(fn ($operation) => $operation === 'edit') // <-- YES LINE ADD KAREIN
            ->action(function (Get $get, Set $set) {
                $set('is_editing', ! $get('is_editing'));
            })
    ),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(255)
                            ->columnSpan(1),
                            
                        Placeholder::make('current_balance')
                            ->label("Receivable Balance")
                            // Sirf edit form me show hoga jahan record mojood ho
                            ->visible(fn($record) => $record !== null)
                            ->content(function ($record) {
                                // Customer ke record se balance liya
                                $balance = (float) $record->current_balance;

                                if ($balance > 0) {
                                    // Agar udhar hai to Red (Tumhare diye hue reference ke hisaab se)
                                    return new HtmlString(
                                        '<span style="color: #ef4444; font-size: 1.5rem; font-weight: 800;">Rs. ' . number_format($balance, 2) . '</span>'
                                    );
                                } else {
                                    // Agar 0 hai to Green (Tumhare diye hue reference ke hisaab se)
                                    return new HtmlString(
                                        '<span style="color: #10b981; font-size: 1.5rem; font-weight: 800;">Rs. ' . number_format($balance, 2) . '</span>'
                                    );
                                }
                            }),
                        
                        Textarea::make('address')
                            ->label('Shop / Home Address')
                            
                            ->columnSpanFull(),

                        TextInput::make('opening_balance')
                            ->label('Opening Balance')
                            ->numeric()
                            ->visible(fn(Get $get) => $get('is_editing'))
                            ->default(0.00)
                            ->prefix('Rs.')
                            ->visible(fn (string $operation, Get $get) => $operation === 'create' || $get('is_editing'))
                            // ->helperText('Agar is customer ne pehle se aapke paise dene hain toh yahan likhein.')
                            ->columnSpanFull(),
                            
                            // ->dehydrated()
                            // ->readOnly(),

                        // Placeholder::make('current_balance'),






                        // Textarea::make('address')
                        //     ->label('Shop / Home Address')
                        //     ->maxLength(65535)
                        //     ->columnSpanFull(),

                        // Textarea::make('notes')
                        //     ->label('Extra Notes / Remarks')
                        //     ->maxLength(65535)
                        //     ->columnSpanFull(),
                        // 3. Address field (sirf tab dikhegi jab is_editing true ho)
                        

                        // 4. Notes field (sirf tab dikhegi jab is_editing true ho)
                        Textarea::make('notes')
                            ->label('Extra Notes / Remarks')
                            ->visible(fn(Get $get) => $get('is_editing'))
                            ->visible(fn (string $operation, Get $get) => $operation === 'create' || $get('is_editing'))
                            ->columnSpanFull(),
                    ])->columns(3)->columnSpanFull(),
            ]);
    }
}
