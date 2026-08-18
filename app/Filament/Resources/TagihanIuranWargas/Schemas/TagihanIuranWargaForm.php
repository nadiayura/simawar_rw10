<?php

namespace App\Filament\Resources\TagihanIuranWargas\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagihanIuranWargaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('warga_nik')
                    ->label('NIK Warga')
                    ->maxLength(16)
                    ->hidden(),
                Select::make('iuran_id')
                    ->label('Iuran')
                    ->options(function () {
                        return \App\Models\Iuran::query()
                            ->get()
                            ->mapWithKeys(function ($iuran) {
                                return [$iuran->iuran_id => $iuran->nama_iuran.' - '.$iuran->jumlah_default];
                            });
                    })
                    ->required(),
                Select::make('periode_iuran_id')
                    ->label('Periode')
                    ->options(function () {
                        $map = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

                        return \App\Models\PeriodeIuran::query()
                            ->orderBy('tahun')
                            ->orderBy('bulan')
                            ->get()
                            ->mapWithKeys(function ($p) use ($map) {
                                $label = ($map[(int) $p->bulan] ?? $p->bulan).' '.$p->tahun;

                                return [$p->periode_iuran_id => $label];
                            });
                    })
                    ->required(),
                TextInput::make('nominal_tagihan')
                    ->required()
                    ->numeric(),
                Select::make('status_id')
                    ->label('Status')
                    ->options(function () {
                        return \App\Models\Status::query()
                            ->where('fitur', 'keuangan')
                            ->orderBy('keterangan')
                            ->pluck('keterangan', 'status_id')
                            ->toArray();
                    })
                    ->default(fn () => \App\Models\Status::idForFitur('keuangan', 'Belum bayar'))
                    ->required(),
                DatePicker::make('tanggal_lunas'),
            ]);
    }
}
