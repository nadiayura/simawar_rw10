<?php

namespace App\Filament\Warga\Resources\Pengaduans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use Filament\Forms\Components\FileUpload;

class PengaduanForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $warga = $user?->warga;

        // Cari tenant berdasarkan id_rt dari warga
        $tenant = null;
        if ($warga && $warga->id_rt) {
            $tenant = Tenant::where('no_rt', $warga->id_rt)->first();
        }

        return $schema
            ->components([
                // Hidden field untuk tenant_id yang otomatis diisi berdasarkan id_rt warga
                Hidden::make('tenant_id')
                    ->default($tenant?->id),

                DatePicker::make('tgl_pengajuan')
                    ->required()
                    ->default(now()),

                // Hidden field untuk id_warga yang otomatis diisi dari user login
                Hidden::make('id_warga')
                    ->default($user?->warga_id),

                Select::make('jenis_pengaduan')
                    ->options([
                        'infrastruktur' => 'Infrastruktur',
                        'kebersihan' => 'Kebersihan',
                        'keamanan' => 'Keamanan',
                        'sosial' => 'Sosial',
                        'kesehatan' => 'Kesehatan',
                        'pendidikan' => 'Pendidikan',
                        'ekonomi' => 'Ekonomi',
                        'lainnya' => 'Lainnya',
                    ])
                    ->required(),

                TextInput::make('jdl_pengaduan')
                    ->label('Judul Pengaduan')
                    ->required(),

                // Hidden field untuk status dengan default pending
                Hidden::make('status')
                    ->default('pending'),

                Textarea::make('detail_pengaduan')
                    ->label('Detail Pengaduan')
                    ->required()
                    ->columnSpanFull(),

                FileUpload::make('bukti')
                    ->required()
                    ->label('Bukti')
                    ->image()
                    ->directory('public/Pengaduan')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
            ]);
    }
}
