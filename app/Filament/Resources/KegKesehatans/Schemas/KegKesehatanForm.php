<?php

namespace App\Filament\Resources\KegKesehatans\Schemas;

use App\Models\KegKesehatan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;

class KegKesehatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('tenant_id')
                    ->default(function () {
                        $tenant = Filament::getTenant();
                        return $tenant ? $tenant->id : null;
                    }),

                Select::make('jenis_kegiatan')
                    ->label('Jenis Kegiatan')
                    ->options(KegKesehatan::getJenisKegiatanOptions())
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('aktivitas_dilakukan', null)),

                TextInput::make('nama_kegiatan')
                    ->label('Nama Kegiatan')
                    ->required(),

                DatePicker::make('tgl')
                    ->label('Tanggal')
                    ->required(),

                TextInput::make('penanggung_jawab')
                    ->label('Penanggung Jawab')
                    ->required(),

                Select::make('status_kegiatan')
                    ->label('Status Kegiatan')
                    ->options(KegKesehatan::getStatusKegiatanOptions())
                    ->default('Selesai')
                    ->required(),

                TextInput::make('bayi')
                    ->label('Jumlah Bayi')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->statePath('rincian_peserta.bayi')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $total = ($get('rincian_peserta.anak') ?? 0) +
                                ($get('rincian_peserta.bayi') ?? 0) +
                                ($get('rincian_peserta.ibu_hamil') ?? 0) +
                                ($get('rincian_peserta.remaja') ?? 0);
                        $set('jumlah_peserta', $total);
                    }),

                TextInput::make('ibu_hamil')
                    ->label('Jumlah Ibu Hamil')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->statePath('rincian_peserta.ibu_hamil')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $total = ($get('rincian_peserta.anak') ?? 0) +
                                ($get('rincian_peserta.bayi') ?? 0) +
                                ($get('rincian_peserta.ibu_hamil') ?? 0) +
                                ($get('rincian_peserta.remaja') ?? 0);
                        $set('jumlah_peserta', $total);
                    }),

                TextInput::make('remaja')
                    ->label('Jumlah Remaja')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->statePath('rincian_peserta.remaja')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $total = ($get('rincian_peserta.anak') ?? 0) +
                                ($get('rincian_peserta.bayi') ?? 0) +
                                ($get('rincian_peserta.ibu_hamil') ?? 0) +
                                ($get('rincian_peserta.remaja') ?? 0);
                        $set('jumlah_peserta', $total);
                    }),

                TextInput::make('jumlah_peserta')
                    ->label('Total Peserta')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(true),

                Select::make('aktivitas_dilakukan')
                    ->label('Aktivitas yang Dilakukan')
                    ->options(function ($get) {
                        $jenisKegiatan = $get('jenis_kegiatan');
                        if (!$jenisKegiatan) {
                            return [];
                        }
                        return KegKesehatan::getAktivitasOptions($jenisKegiatan);
                    })
                    ->required(),

                Textarea::make('hasil_pelaksanaan')
                    ->required()
                    ->label('Hasil Pelaksanaan')
                    ->rows(4)
                    ->columnSpanFull(),

                FileUpload::make('dokumentasi')
                    ->label('Dokumentasi')
                    ->image()
                    ->directory('public/KegKesehatan')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->multiple()
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }
}
