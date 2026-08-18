<?php

namespace App\Filament\Resources\KetuaRts\Schemas;

use App\Models\NoRt;
use App\Models\Warga;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class KetuaRtForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('no_rt_id')
                    ->label('Nomor RT')
                    ->default(fn () => request()->get('no_rt_id'))
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $noRt = NoRt::find($state);
                            if ($noRt) {
                                $set('nama', $noRt->nomor);
                            }
                        }
                    })
                    ->required(),
                Select::make('jabatan')
                    ->label('Jabatan')
                    ->options([
                        'Ketua RT' => 'Ketua RT',
                        'Sekretaris RT' => 'Sekretaris RT',
                        'Bendahara RT' => 'Bendahara RT',
                    ])
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set, $record, callable $get) {
                        if (! $state || $record) {
                            return;
                        }

                        // Cek apakah jabatan sudah ada yang mengisi
                        $rtId = $get('no_rt_id') ?? request()->get('no_rt_id');
                        if (! $rtId && auth()->user()?->role?->isRT() && auth()->user()?->warga?->no_rt_id) {
                            $rtId = auth()->user()->warga->no_rt_id;
                        }

                        $exists = \App\Models\KetuaRt::query()
                            ->where('jabatan', $state)
                            ->when($rtId, fn ($q) => $q->where('no_rt_id', $rtId))
                            ->where('is_active', true)
                            ->exists();

                        if ($exists) {
                            // Reset nilai jabatan
                            $set('jabatan', null);

                            // Tampilkan notifikasi
                            Notification::make()
                                ->title('Jabatan sudah terisi')
                                ->body('Jabatan sudah terisi pada RT '.(string) $rtId.'. Silakan nonaktifkan jabatan aktif terlebih dahulu atau pilih jabatan lain.')
                                ->icon('heroicon-o-exclamation-triangle')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
                Select::make('warga_nik')
                    ->label('Nama Warga')
                    ->options(function (callable $get) {
                        $user = request()->user();
                        $rtId = $get('no_rt_id') ?? request()->get('no_rt_id');

                        $query = Warga::query();

                        if ($rtId) {
                            $query->where('no_rt_id', $rtId);
                        } elseif ($user && $user->role && $user->role->isRT() && $user->warga) {
                            $query->where('no_rt_id', $user->warga->no_rt_id);
                        }

                        return $query->orderBy('nama')->get()->mapWithKeys(function ($w) {
                            return [$w->warga_nik => "{$w->no_rt_id} - {$w->nama}"];
                        })->toArray();
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $w = Warga::find($state);
                            if ($w) {
                                if ($w->alamat) {
                                    $set('alamat', $w->alamat);
                                }
                                if ($w->no_hp) {
                                    $set('no_hp', $w->no_hp);
                                }
                            }
                        }
                    })
                    ->searchable()
                    ->required(),
                TextInput::make('alamat')
                    ->label('Alamat')
                    ->disabled()
                    ->dehydrated()
                    ->default(function ($get) {
                        $nik = $get('warga_nik');

                        return optional(\App\Models\Warga::find($nik))->alamat;
                    })
                    ->afterStateHydrated(function (callable $set, callable $get) {
                        $nik = $get('warga_nik');
                        if (! $nik) {
                            return;
                        }

                        $alamat = optional(\App\Models\Warga::find($nik))->alamat;
                        if ($alamat) {
                            $set('alamat', $alamat);
                        }
                    }),
                TextInput::make('no_hp')
                    ->label('No HP Aktif')
                    ->required(),
                DatePicker::make('periode_mulai')
                    ->label('Periode Mulai'),
                DatePicker::make('periode_selesai')
                    ->label('Periode Selesai'),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
