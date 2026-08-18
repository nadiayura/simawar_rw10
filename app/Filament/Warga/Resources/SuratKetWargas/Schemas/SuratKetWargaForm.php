<?php

namespace App\Filament\Warga\Resources\SuratKetWargas\Schemas;

use App\Models\JenisSurat;
use App\Models\Status;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class SuratKetWargaForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $needsTanggalAcara = function ($get): bool {
            $id = $get('jenis_surat_id');
            if (! $id) {
                return false;
            }
            $nama = strtolower(trim((string) (JenisSurat::find($id)?->nama_surat)));

            return str_contains($nama, 'izin keramaian') || str_contains($nama, 'perizinan menikah');
        };

        return $schema
            ->components([
                Hidden::make('warga_nik')->default($user?->warga_nik),
                DatePicker::make('tgl_pengajuan')->default(now())->required(),
                Select::make('jenis_surat_id')
                    ->label('Jenis Surat')
                    ->options(function () {
                        return JenisSurat::query()->pluck('nama_surat', 'jenis_surat_id')->toArray();
                    })
                    ->default(fn () => request()->query('jenis'))
                    ->live()
                    ->required(),
                DatePicker::make('tgl_acara_mulai')
                    ->label('Tanggal Acara Dilaksanakan')
                    ->visible($needsTanggalAcara)
                    ->required($needsTanggalAcara),
                DatePicker::make('tgl_acara_selesai')
                    ->label('Tanggal Selesai Acara')
                    ->visible($needsTanggalAcara)
                    ->required($needsTanggalAcara)
                    ->minDate(fn ($get) => $get('tgl_acara_mulai')),
                Textarea::make('keperluan')->required()->columnSpanFull(),
                FileUpload::make('dok_pendukung')
                    ->hint(function ($get) {
                        $id = $get('jenis_surat_id');
                        $jenis = $id ? JenisSurat::find($id) : null;

                        return $jenis
                            ? "{$jenis->deskripsi}. Format file: JPG / PNG"
                            : 'Format file: JPG / PNG';
                    })
                    ->label('Dokumen Pendukung')
                    ->image()
                    ->directory('public/SuratKetWarga')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->multiple()
                    ->reorderable()
                    ->columnSpanFull()
                    ->required(),
                Hidden::make('status_id')->default(Status::idForFitur('surat_ket_warga', 'Diajukan')),
            ]);
    }
}
