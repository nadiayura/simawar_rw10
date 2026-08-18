<?php

namespace App\Filament\Resources\KegKesehatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KegKesehatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tgl', 'desc')
            ->columns([
                TextColumn::make('tgl')
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
                TextColumn::make('jenis_kegiatan')
                    ->searchable(),
                TextColumn::make('nama_kegiatan')
                    ->searchable(),
                TextColumn::make('tgl')
                    ->date()
                    ->sortable(),
                TextColumn::make('penanggung_jawab')
                    ->searchable(),
                TextColumn::make('jumlah_peserta')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dokumentasi')
                    ->searchable(),
                TextColumn::make('status.keterangan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'berlangsung' => 'info',
                        'dijadwalkan' => 'gray',
                        'selesai' => 'success',
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

                        if ($user->role->isRT() && strtolower((string) $user->role->name) === 'rt') {
                            return false;
                        }

                        return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang dipilih')
                        ->modalHeading('Hapus Kegiatan Kesehatan Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus kegiatan kesehatan yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Hapus')
                        ->modalCancelActionLabel('Batal')
                        ->visible(function () {
                            $user = auth()->user();
                            if (! $user || ! $user->role) {
                                return false;
                            }

                            if ($user->role->isRT() && strtolower((string) $user->role->name) === 'rt') {
                                return false;
                            }

                            return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
                        }),
                ]),
            ]);
    }
}
