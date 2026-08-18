<?php

namespace App\Filament\Resources\NoRts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NoRtForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nomor')
                    ->label('No. RT')
                    ->required(),
                TextInput::make('rw')
                    ->disabled()
                    ->required()
                    ->default('010'),
            ]);
    }
}
