<?php

namespace App\Filament\Resources\KegKarangTarunas\Schemas;

use App\Models\Status;
use App\Models\Warga;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class KegKarangTarunaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kegiatan')
                    ->required(),
                Textarea::make('deskripsi')
                    ->columnSpanFull(),
                DatePicker::make('tanggal')
                    ->required(),
                Select::make('penanggung_jawab')
                    ->label('Penanggung Jawab')
                    ->options(function () {
                        $user = Auth::user();
                        $query = Warga::query();

                        if ($user && $user->role && $user->role->isRT() && $user->warga) {
                            $query->where('no_rt_id', $user->warga->no_rt_id);
                        }

                        return $query->orderBy('nama')->get()->mapWithKeys(function ($w) {
                            return [$w->warga_nik => "{$w->no_rt_id} - {$w->nama}"];
                        })->toArray();
                    })
                    ->searchable()
                    ->required(),
                Select::make('status_id')
                    ->label('Status Kegiatan')
                    ->options(function () {
                        return Status::query()
                            ->where('fitur', 'keg_warga')
                            ->orderBy('keterangan')
                            ->pluck('keterangan', 'status_id')
                            ->toArray();
                    })
                    ->required(),
                FileUpload::make('dokumentasi')
                    ->label('Dokumentasi')
                    ->image()
                    ->directory('public/KegKarangTarunas')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->multiple()
                    ->reorderable()
                    ->columnSpanFull()
                    ->required(),
            ]);
    }
}
