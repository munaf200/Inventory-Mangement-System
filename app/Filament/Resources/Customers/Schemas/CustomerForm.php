<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->description('Customer ki bunyadi malomat enter karein.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Customer / Shop Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('opening_balance')
                            ->label('Opening Balance (Pehle ka Baqaya)')
                            ->numeric()
                            ->default(0.00)
                            ->prefix('Rs.')
                            ->helperText('Agar is customer ne pehle se aapke paise dene hain toh yahan likhein.')
                            ->columnSpan(2),

                        Textarea::make('address')
                            ->label('Shop / Home Address')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Extra Notes / Remarks')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
