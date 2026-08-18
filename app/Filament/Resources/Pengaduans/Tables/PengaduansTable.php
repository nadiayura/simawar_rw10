<?php

namespace App\Filament\Resources\Pengaduans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengaduansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tgl_pengajuan')
                    ->date()
                    ->sortable(),
                TextColumn::make('warga.nama')
                    ->label('Nama Warga')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('bukti')
                    ->label('Bukti')
                    ->visibility('public'),
                TextColumn::make('jenisPengaduan.nama')
                    ->label('Jenis Pengaduan')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'keamanan' => 'primary',
                        'kebersihan' => 'info',
                        'kesehatan' => 'success',
                        'infrastruktur' => 'danger',
                        'sosial' => 'danger',
                        'pendidikan' => 'danger',
                    }),
                TextColumn::make('jdl_pengaduan')
                    ->label('Judul Pengaduan'),
                TextColumn::make('solusi_admin')
                    ->label('Solusi Admin')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status.keterangan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'zinc',
                        'Diverifikasi' => 'info',
                        'Diproses' => 'primary',
                        'Ditolak' => 'danger',
                        'Selesai' => 'success',
                    }),
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
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
