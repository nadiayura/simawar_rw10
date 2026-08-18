<?php

namespace App\Filament\Resources\KegKarangTarunas\Tables;

use App\Models\NoRt;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KegKarangTarunasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('keg_karang_taruna_id')
                    ->searchable()
                    ->hidden(),
                TextColumn::make('tanggal')
                    ->label('Bulan')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        $month = (int) \Carbon\Carbon::parse($state)->format('n');
                        $map = [
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ];

                        return $map[$month] ?? (string) $month;
                    })
                    ->sortable(),
                TextColumn::make('nama_kegiatan')
                    ->searchable(),
                TextColumn::make('tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('pjWarga.no_rt_id')
                    ->label('RT')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        $nomor = NoRt::find($state)?->nomor ?? $state;

                        $nomor = (string) $nomor;

                        if (str_starts_with($nomor, 'RT-')) {
                            return $nomor;
                        }

                        return 'RT '.str_pad($nomor, 3, '0', STR_PAD_LEFT);
                    })
                    ->sortable(),
                TextColumn::make('pjWarga.nama')
                    ->label('Penanggung Jawab')
                    ->searchable(),
                TextColumn::make('status.keterangan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'diajukan' => 'warning',
                        'diproses' => 'info',
                        'disetujui' => 'info',
                        'ditolak' => 'danger',
                        'selesai' => 'success',
                        'terjadwal' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label(function ($record) {
                        $statusName = strtolower($record->status->keterangan ?? '');

                        if (in_array($statusName, ['dijadwalkan', 'terjadwal', 'berlangsung'], true)) {
                            return 'Lengkapi Info Kegiatan';
                        }

                        return 'Edit';
                    })
                    ->visible(function () {
                        $user = auth()->user();
                        if (! $user || ! $user->role) {
                            return false;
                        }

                        return $user->role->isAdmin() || $user->role->isRT();
                    }),
                DeleteAction::make()
                    ->modalHeading('Hapus Kegiatan Karang Taruna')
                    ->modalDescription('Apakah Anda yakin ingin menghapus kegiatan ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Hapus')
                    ->modalCancelActionLabel('Batal')
                    ->visible(function () {
                        $user = auth()->user();
                        if (! $user || ! $user->role) {
                            return false;
                        }

                        return $user->role->isAdmin() || $user->role->isRT();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang dipilih')
                        ->modalHeading('Hapus Kegiatan Karang Taruna Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus kegiatan Karang Taruna yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Hapus')
                        ->modalCancelActionLabel('Batal')
                        ->visible(function () {
                            $user = auth()->user();
                            if (! $user || ! $user->role) {
                                return false;
                            }

                            return $user->role->isAdmin() || $user->role->isRT();
                        }),
                ]),
            ]);
    }
}
