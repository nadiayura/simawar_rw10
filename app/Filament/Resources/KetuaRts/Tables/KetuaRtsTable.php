<?php

namespace App\Filament\Resources\KetuaRts\Tables;

use App\Filament\Resources\KetuaRts\KetuaRtResource;
use App\Models\NoRt;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KetuaRtsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_rt_id')
                    ->formatStateUsing(fn ($state) => NoRt::find($state)?->nomor)
                    ->label('Nomor RT')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Ketua RT' => 'success',
                        'Sekretaris RT' => 'info',
                        'Bendahara RT' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('warga.nama')
                    ->label('Nama Warga')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_hp')
                    ->label('No HP Aktif ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('periode_mulai')
                    ->label('Periode Mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('periode_selesai')
                    ->label('Periode Selesai')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
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
                CreateAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => KetuaRtResource::canEdit($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // ->visible(fn () => !auth()->user()?->role?->isRT()),
                ]),
            ]);
    }
}
