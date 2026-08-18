<?php

namespace App\Filament\Resources\RekapKeuangans\Schemas;

use App\Models\NoRt;
use App\Models\PembayaranMidtrans;
use App\Models\Status;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema as FilamentSchema;
use Illuminate\Support\Facades\Schema as DbSchema;

class RekapKeuanganForm
{
    public static function configure(FilamentSchema $schema): FilamentSchema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal')
                    ->required(),
                Select::make('no_rt_id')
                    ->label('Nomor RT')
                    ->options(function () {
                        $user = request()->user();
                        $query = NoRt::query()->orderBy('nomor');
                        if ($user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id) {
                            $query->where('no_rt_id', $user->warga->no_rt_id);
                        }

                        return $query->get()->mapWithKeys(function ($rt) {
                            $label = 'RT '.str_pad((string) $rt->nomor, 3, '0', STR_PAD_LEFT);

                            return [$rt->no_rt_id => $label];
                        })->toArray();
                    })
                    ->default(fn () => request()->get('no_rt_id') ?? optional(request()->user()?->warga)->no_rt_id)
                    ->searchable()
                    ->required()
                    ->hidden(fn () => ! DbSchema::hasColumn('rekap_keuangan', 'no_rt_id'))
                    ->dehydrated(fn () => DbSchema::hasColumn('rekap_keuangan', 'no_rt_id')),
                Select::make('jenis')
                    ->options([
                        'masuk' => 'Masuk',
                        'keluar' => 'Keluar',
                    ])
                    ->default(fn () => request()->query('jenis'))
                    ->required()
                    ->reactive(),
                Select::make('sumber')
                    ->options([
                        'iuran' => 'Iuran',
                        'donasi' => 'Donasi',
                    ])
                    ->required()
                    ->dehydrated(fn ($get) => $get('jenis') === 'masuk')
                    ->reactive(),
                TextInput::make('nominal')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((float) $state, 2, ',', '.'))
                    ->required(),
                TextInput::make('PembayaranMidtrans_id')
                    ->default(fn () => DbSchema::hasColumn('rekap_keuangan', 'PembayaranMidtrans_id')
                        ? PembayaranMidtrans::query()
                            ->where('status_id', Status::idByName('settlement'))
                            ->latest('updated_at')
                            ->value('PembayaranMidtrans_id')
                        : null)
                    ->nullable()
                    ->hidden()
                    ->dehydrated(fn ($get) => $get('jenis') === 'masuk' && DbSchema::hasColumn('rekap_keuangan', 'PembayaranMidtrans_id')),
                Select::make('metode')
                    ->options([
                        'cash' => 'Cash',
                        'transfer' => 'Transfer',
                    ])
                    ->required(),
                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3)
                    ->required()
                    ->hidden(fn () => ! DbSchema::hasColumn('rekap_keuangan', 'keterangan'))
                    ->dehydrated(fn () => DbSchema::hasColumn('rekap_keuangan', 'keterangan')),
                FileUpload::make('bukti')
                    ->required()
                    ->label('Bukti')
                    ->image()
                    ->disk('public')
                    ->directory('Keuangan')
                    ->visibility('public')
                    ->multiple()
                    ->reorderable()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),

            ]);
    }
}
