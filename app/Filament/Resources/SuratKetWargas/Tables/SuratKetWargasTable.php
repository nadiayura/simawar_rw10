<?php

namespace App\Filament\Resources\SuratKetWargas\Tables;

use App\Filament\Resources\SuratKetWargas\SuratKetWargaResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuratKetWargasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('warga.nama')
                    ->label('Nama Warga')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jenisSurat.nama_surat')
                    ->label('Nama Jenis Surat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tgl_pengajuan')
                    ->date()
                    ->sortable(),
                TextColumn::make('tgl_selesai')
                    ->label('Tgl Selesai')
                    ->date()
                    ->sortable(),
                TextColumn::make('status.keterangan')
                    ->label('Status')
                    ->badge()
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
                Action::make('view')
                    ->url(fn ($record) => SuratKetWargaResource::getUrl('view', ['record' => $record->surat_ket_warga_id])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
