<?php

namespace App\Filament\Resources\SuratKetWargas\Schemas;

use App\Models\JenisSurat;
use App\Models\Status;
use App\Models\Warga;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class SuratKetWargaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('warga_nik')
                    ->label('Nama Warga')
                    ->options(function () {
                        $user = Auth::user();
                        if (! $user || ! $user->role) {
                            return [];
                        }

                        $query = Warga::query();

                        // Role-only filter: RT terbatas ke RT miliknya, RW/Admin bebas
                        if ($user->role->isRT() && $user->warga) {
                            $query->where('no_rt_id', $user->warga->no_rt_id);
                        }

                        return $query->get()->mapWithKeys(function ($w) {
                            return [$w->warga_nik => "RT {$w->no_rt_id} - {$w->nama}"];
                        })->toArray();
                    })
                    ->searchable()
                    ->required(),
                Select::make('jenis_surat_id')
                    ->label('Jenis Surat')
                    ->options(function () {
                        return JenisSurat::query()->pluck('nama_surat', 'jenis_surat_id')->toArray();
                    })
                    ->required(),
                DatePicker::make('tgl_pengajuan')
                    ->required(),
                Textarea::make('keperluan')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('dok_pendukung')
                    ->label('Dokumen Pendukung')
                    ->helperText(function ($get) {
                        $id = $get('jenis_surat_id');
                        if (! $id) {
                            return null;
                        }
                        $jenis = JenisSurat::find($id);

                        return $jenis?->deskripsi;
                    })
                    ->directory('public/SuratKetWarga')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->multiple()
                    ->reorderable()
                    ->columnSpanFull()
                    ->required(),
                Select::make('status_id')
                    ->label('Status')
                    ->options(fn () => Status::query()->where('fitur', 'surat')->orderBy('keterangan')->pluck('keterangan', 'status_id')->toArray())
                    ->searchable()
                    ->default(fn () => Status::idForFitur('surat', 'Diajukan'))
                    ->required()
                    ->live(),
                DatePicker::make('tgl_selesai')
                    ->label('Tgl Selesai')
                    ->visible(fn ($get) => strtolower((string) optional(Status::find($get('status_id')))->keterangan) === 'selesai')
                    ->required(fn ($get) => strtolower((string) optional(Status::find($get('status_id')))->keterangan) === 'selesai'),
                Textarea::make('catatan_admin')
                    ->columnSpanFull(),
            ]);
    }
}
