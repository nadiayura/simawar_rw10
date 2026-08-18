<?php

namespace App\Filament\Resources\Strukturals\Schemas;

use App\Models\Warga;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StrukturalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->disabled(),
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
                                $set('nama', $w->nama);
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
                Textarea::make('alamat')
                    ->label('Alamat')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('no_hp')
                    ->label('No HP Aktif')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('jabatan')
                    ->options([
                        'Ketua RW' => 'Ketua RW',
                        'Sekretaris RW' => 'Sekretaris RW',
                        'Bendahara RW' => 'Bendahara RW',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $record, callable $get) {
                        if (! $state || $record) {
                            return;
                        }

                        $exists = \App\Models\Struktural::query()
                            ->where('jabatan', $state)
                            ->where('is_active', true)
                            ->exists();

                        if ($exists) {
                            $set('jabatan', null);

                            \Filament\Notifications\Notification::make()
                                ->title('Jabatan RW sudah terisi')
                                ->body('Silakan nonaktifkan jabatan aktif terlebih dahulu atau pilih jabatan lain.')
                                ->icon('heroicon-o-exclamation-triangle')
                                ->danger()
                                ->actions([
                                    Action::make('close')
                                        ->label('Tutup')
                                        ->button()
                                        ->color('danger'),
                                ])
                                ->persistent()
                                ->send();
                        }

                        $nik = $get('warga_nik');
                        if ($nik && str_contains(strtolower($state), 'ketua rt')) {
                            $w = \App\Models\Warga::find($nik);
                            $rtId = $w?->no_rt_id;
                            if ($rtId) {
                                $dupRt = \App\Models\Struktural::query()
                                    ->ketuaRt()
                                    ->where('is_active', true)
                                    ->whereHas('warga', fn ($q) => $q->where('no_rt_id', $rtId))
                                    ->exists();
                                if ($dupRt) {
                                    $set('jabatan', null);
                                    \Filament\Notifications\Notification::make()
                                        ->title('Ketua RT sudah terisi')
                                        ->body('Ketua RT pada RT ini sudah terisi. Nonaktifkan data aktif terlebih dahulu atau pilih jabatan lain.')
                                        ->icon('heroicon-o-exclamation-triangle')
                                        ->danger()
                                        ->persistent()
                                        ->send();
                                }
                            }
                        }
                    }),
                DatePicker::make('periode_mulai')
                    ->required(),
                DatePicker::make('periode_selesai')
                    ->required(),
                Textarea::make('deskripsi'),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
                TextInput::make('urutan')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->hidden(),
            ]);
    }
}
