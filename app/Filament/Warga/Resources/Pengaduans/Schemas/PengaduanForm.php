<?php

namespace App\Filament\Warga\Resources\Pengaduans\Schemas;

use App\Models\Status;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
// no tenant usage
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PengaduanForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();

        return $schema
            ->components([
                Hidden::make('tgl_pengajuan')
                    ->default(fn () => now()->toDateString()),

                Hidden::make('warga_nik')
                    ->default($user?->warga_nik),

                Select::make('jenis_pengaduan_id')
                    ->label('Jenis Pengaduan')
                    ->options(fn () => \App\Models\JenisPengaduan::query()->orderBy('nama')->pluck('nama', 'id')->toArray())
                    ->searchable()
                    ->required(),

                TextInput::make('jdl_pengaduan')
                    ->label('Judul Pengaduan')
                    ->required(),

                Hidden::make('status_id')
                    ->default(Status::idForFitur('pengaduan', 'Pending')),

                Textarea::make('detail_pengaduan')
                    ->label('Detail Pengaduan')
                    ->required()
                    ->columnSpanFull(),

                FileUpload::make('bukti')
                    ->hint('Format file: JPEG, PNG, WebP. Maksimal ukuran: 2MB')
                    ->required()
                    ->label('Bukti')
                    ->image()
                    ->directory('public/Pengaduan')
                    ->columnSpanFull()
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048),
            ]);
    }
}
