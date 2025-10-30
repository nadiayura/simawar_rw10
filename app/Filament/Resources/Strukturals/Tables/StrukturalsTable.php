<?php

namespace App\Filament\Resources\Strukturals\Tables;

use App\Filament\Resources\Strukturals\StrukturalResource;
use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StrukturalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable()
                    ->width(80),

                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->size(50)
                    ->defaultImageUrl(url('/images/default-avatar.png')),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('warga.nama')
                    ->label('Warga Terpilih')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tidak ada')
                    ->description(fn ($record) => $record->warga ? 'RT ' . $record->warga->no_rt : null),

                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Ketua RW') => 'success',
                        str_contains($state, 'Ketua RT') => 'info',
                        default => 'gray',
                    }),



                TextColumn::make('periode')
                    ->label('Periode')
                    ->getStateUsing(fn ($record) => $record->periode_mulai . ' - ' . $record->periode_selesai)
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jabatan')
                    ->label('Jabatan')
                    ->options([
                        'Ketua RW' => 'Ketua RW',
                        'Ketua RT 01' => 'Ketua RT 01',
                        'Ketua RT 02' => 'Ketua RT 02',
                        'Ketua RT 03' => 'Ketua RT 03',
                        'Ketua RT 04' => 'Ketua RT 04',
                        'Ketua RT 05' => 'Ketua RT 05',
                        'Ketua RT 06' => 'Ketua RT 06',
                        'Ketua RT 07' => 'Ketua RT 07',
                        'Ketua RT 08' => 'Ketua RT 08',
                        'Ketua RT 09' => 'Ketua RT 09',
                        'Ketua RT 10' => 'Ketua RT 10',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
                ])
            ->recordActions([
                ActionsEditAction::make()
                    ->visible(fn ($record) => StrukturalResource::canEdit($record)),
            ])
            ->toolbarActions([
                ActionsBulkActionGroup::make([
                    ActionsDeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->role?->isRW() ?? false),
                ]),
            ])
            ->defaultSort('urutan', 'asc');
    }
}
