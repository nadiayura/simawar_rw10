<?php

namespace App\Filament\Resources\Pengaduans\Schemas;

use App\Models\Warga;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PengaduanForm
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
                DatePicker::make('tgl_pengajuan')
                    ->required(),
                Select::make('id_warga')
                    ->label('Nama Warga')
                    ->relationship('warga', 'nama')
                    ->getOptionLabelFromRecordUsing(fn (Warga $record) => "RT {$record->id_rt} - {$record->nama}")
                    ->getSearchResultsUsing(function (string $search) {
                        $user = Auth::user();
                        $tenant = Filament::getTenant();

                        if (!$user || !$user->role) {
                            return [];
                        }

                        $query = Warga::where('nama', 'like', "%{$search}%");

                        if ($user->role->isRW()) {
                            // RW dapat memilih warga dari semua RT dalam RW-nya
                            if ($tenant) {
                                $tenantRwPadded = str_pad($tenant->rw, 3, '0', STR_PAD_RIGHT);
                                $query->where('rw', $tenantRwPadded);
                            }
                        } elseif ($user->role->isRT()) {
                            // RT hanya dapat memilih warga dari RT sendiri
                            if ($tenant) {
                                $query->where('id_rt', $tenant->no_rt)
                                      ->where('rw', str_pad($tenant->rw, 3, '0', STR_PAD_RIGHT));
                            }
                        } elseif (!$user->role->isAdmin()) {
                            // Role lain tidak dapat memilih warga
                            return [];
                        }

                        return $query->limit(50)->get()->mapWithKeys(function ($warga) {
                            return [$warga->id => "RT {$warga->id_rt} - {$warga->nama}"];
                        })->toArray();
                    })
                    ->getOptionLabelsUsing(function (array $values) {
                        return Warga::whereIn('id', $values)->get()->mapWithKeys(function ($warga) {
                            return [$warga->id => "RT {$warga->id_rt} - {$warga->nama}"];
                        })->toArray();
                    })
                    ->searchable()
                    ->required(),
                Select::make('jenis_pengaduan')
                    ->options([
                    'infrastruktur' => 'Infrastruktur',
                    'kebersihan' => 'Kebersihan',
                    'keamanan' => 'Keamanan',
                    'sosial' => 'Sosial',
                    'kesehatan' => 'Kesehatan',
                    'pendidikan' => 'Pendidikan',
                    'ekonomi' => 'Ekonomi',
                    'lainnya' => 'Lainnya',])
                    ->required(),
                Textarea::make('jdl_pengaduan')
                    ->required()
                    ->label('Judul Pengaduan')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
               ])
                    ->default('pending')
                    ->required(),
                Textarea::make('detail_pengaduan')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('bukti')
                    ->required()
                    ->label('Bukti')
                    ->image()
                    ->directory('public/Pengaduan')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048), // 2MB max
            ]);
    }
}
