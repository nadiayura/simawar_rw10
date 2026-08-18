<?php

namespace App\Filament\Warga\Resources\Pengaduans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PengaduansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pengaduan_id')
                    ->label('No Pengaduan')
                    ->searchable()
                    ->hidden(),
                TextColumn::make('tgl_pengajuan')
                    ->date()
                    ->sortable(),
                TextColumn::make('status.keterangan')
                    ->label('Status')
                    ->badge(
                        fn ($state): string => match (Str::lower((string) $state)) {
                            'pending' => 'primary',
                            'diproses' => 'info',
                            'selesai' => 'success',
                            'ditolak' => 'danger',
                            default => 'gray',
                        })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jenisPengaduan.nama')
                    ->label('Jenis Pengaduan')
                    ->searchable(),
                TextColumn::make('jdl_pengaduan')
                    ->label('Judul Pengaduan')
                    ->searchable(),
                TextColumn::make('solusi_admin')
                    ->label('Solusi Admin')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
