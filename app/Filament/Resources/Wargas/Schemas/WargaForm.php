<?php

namespace App\Filament\Resources\Wargas\Schemas;

use App\Models\Iuran;
use App\Models\NoRt;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WargaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('warga_nik')
                    ->label('NIK')
                    ->maxLength(16)
                    ->dehydrateStateUsing(fn ($state) => $state ? ('WRG-'.$state) : null)
                    ->afterStateHydrated(fn ($state, callable $set) => $state ? $set('warga_nik', (string) str_replace('WRG-', '', (string) $state)) : null)
                    ->required(),
                TextInput::make('nama')
                    ->label('Nama')
                    ->required(),
                Select::make('jenis_kelamin')
                    ->options(['L' => 'L', 'P' => 'P'])
                    ->required(),
                Select::make('agama')
                    ->options([
                        'Islam' => 'Islam',
                        'Kristen' => 'Kristen',
                        'Katolik' => 'Katolik',
                        'Hindu' => 'Hindu',
                        'Buddha' => 'Buddha',
                        'Konghucu' => 'Konghucu',
                    ])
                    ->required(),
                Select::make('status_tinggal')
                    ->options(['Tetap' => 'Tetap', 'Kontrak' => 'Kontrak', 'Sementara' => 'Sementara'])
                    ->required(),
                Textarea::make('alamat')
                    ->required()
                    ->maxLength(255),
                Select::make('no_rt_id')
                    ->label('Nomor RT')
                    ->options(NoRt::query()->pluck('nomor', 'no_rt_id'))
                    ->default(fn () => request()->get('no_rt_id'))
                    ->required(),
                TextInput::make('no_hp')
                    ->tel()
                    ->required()
                    ->maxLength(15),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Select::make('iuran_id')
                    ->label('Iuran')
                    ->options(function () {
                        return Iuran::query()
                            ->get()
                            ->mapWithKeys(function ($iuran) {
                                return [$iuran->iuran_id => $iuran->nama_iuran.' - '.$iuran->jumlah_default];
                            });
                    })
                    ->required(),
            ]);
    }
}
