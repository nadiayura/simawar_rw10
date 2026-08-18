<?php

namespace App\Filament\Resources\Pengaduans\Schemas;

use App\Models\Status;
use App\Models\Warga;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PengaduanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tgl_pengajuan')
                    ->required()
                    ->disabled(fn ($record) => filled($record?->warga_nik)),
                Select::make('warga_nik')
                    ->label('Nama Warga')
                    ->options(fn () => Warga::query()->orderBy('nama')->pluck('nama', 'warga_nik')->toArray())
                    ->preload()
                    ->required()
                    ->disabled(fn ($record) => filled($record?->warga_nik)),
                Select::make('jenis_pengaduan_id')
                    ->label('Jenis Pengaduan')
                    ->relationship('jenisPengaduan', 'nama')
                    ->preload()
                    ->required()
                    ->disabled(fn ($record) => filled($record?->warga_nik)),
                Textarea::make('jdl_pengaduan')
                    ->required()
                    ->label('Judul Pengaduan')
                    ->columnSpanFull()
                    ->disabled(fn ($record) => filled($record?->warga_nik)),
                Select::make('status_id')
                    ->label('Status')
                    ->options(function () {
                        return Status::query()
                            ->where('fitur', 'pengaduan')
                            ->orderBy('keterangan')
                            ->pluck('keterangan', 'status_id')
                            ->toArray();
                    })
                    ->required()
                    ->reactive(),
                Textarea::make('detail_pengaduan')
                    ->required()
                    ->columnSpanFull()
                    ->disabled(fn ($record) => filled($record?->warga_nik)),
                Textarea::make('solusi_admin')
                    ->label('Solusi Admin')
                    ->columnSpanFull()
                    ->rows(3)
                    ->visible(fn ($get) => strtolower(Status::query()->where('status_id', $get('status_id'))->value('keterangan') ?? '') === 'selesai')
                    ->required(fn ($get) => strtolower(Status::query()->where('status_id', $get('status_id'))->value('keterangan') ?? '') === 'selesai'),
                FileUpload::make('bukti')
                    ->required()
                    ->label('Bukti')
                    ->image()
                    ->directory('public/Pengaduan')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048) // 2MB max
                    ->disabled(fn ($record) => filled($record?->warga_nik)),
            ]);
    }
}
