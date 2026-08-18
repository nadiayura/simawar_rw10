<?php

namespace App\Filament\Resources\Iurans\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IuranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_iuran')
                    ->required(),
                TextInput::make('jumlah_default')
                    ->label('Nominal')
                    ->required()
                    ->numeric(),
                Textarea::make('deskripsi')
                    ->columnSpanFull(),
            ]);
    }
}
