<?php

namespace App\Filament\Warga\Resources\SuratKetWargas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SuratKetWargasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenisSurat.nama_surat')
                    ->label('Jenis Surat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tgl_pengajuan')
                    ->date()
                    ->sortable(),
                TextColumn::make('tgl_selesai')
                    ->label('Tgl Selesai')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status.keterangan')
                    ->badge(
                        fn ($state): string => match (Str::lower((string) $state)) {
                            'diajukan' => 'primary',
                            'menunggu verifikasi' => 'warning',
                            'selesai' => 'success',
                            'ditolak' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->sortable(),
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
                    ->label('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
