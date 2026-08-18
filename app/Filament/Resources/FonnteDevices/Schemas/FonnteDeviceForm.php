<?php

namespace App\Filament\Resources\FonnteDevices\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FonnteDeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('device')
                    ->required(),
                TextInput::make('token')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('inactive'),
                DateTimePicker::make('last_synced_at'),
            ]);
    }
}
