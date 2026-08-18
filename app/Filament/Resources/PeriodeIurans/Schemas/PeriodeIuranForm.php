<?php

namespace App\Filament\Resources\PeriodeIurans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PeriodeIuranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tahun')
                    ->label('Tahun')
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->default(fn () => now()->year)
                    ->required(),
                Select::make('bulan')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                    ])
                    ->default(fn () => now()->month)
                    ->required(),
                DatePicker::make('tanggal_jatuh_tempo')
                    ->label('Tanggal Jatuh Tempo')
                    ->displayFormat('d F Y')
                    ->firstDayOfWeek(1)
                    ->helperText('Tanggal jatuh tempo untuk pembayaran iuran')
                    ->required(),
            ]);
    }
}
