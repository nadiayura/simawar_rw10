<?php

namespace App\Filament\Resources\Wargas\Tables;

use App\Filament\Resources\Wargas\WargaResource;
use App\Models\Iuran;
use App\Models\NoRt;
use App\Models\Warga;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WargasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('warga_nik')
                    ->label('NIK')
                    ->formatStateUsing(fn ($state) => Warga::maskedNik($state))
                    ->searchable(),
                TextColumn::make('nama')
                    ->searchable(),
                TextColumn::make('jenis_kelamin'),
                TextColumn::make('status_tinggal'),
                TextColumn::make('no_rt_id')
                    ->label('RT')
                    ->formatStateUsing(fn ($state) => NoRt::find($state)?->nomor)
                    ->searchable(),
                TextColumn::make('no_hp')
                    ->searchable(),
                // TextColumn::make('email')
                //     ->label('Email address')
                //     ->searchable(),
                TextColumn::make('iuran_id')
                    ->label('Iuran')
                    ->formatStateUsing(fn ($state) => Iuran::find($state)?->nama_iuran)
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
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => WargaResource::getUrl('view', ['record' => $record->warga_nik])),
                // Action::make('verifikasi')
                //     ->label('Verifikasi')
                //     ->color('success')
                //     ->icon('heroicon-o-check')
                //     ->visible(fn ($record) => $record->user && $record->user->role && $record->user->role->name === 'tamu')
                //     ->action(function ($record) {
                //         $user = $record->user;
                //         if ($user) {
                //             $user->role_id = 1; // role_id 1 = warga
                //             $user->save();
                //         }
                //     }),
                // Action::make('tolak')
                //     ->label('Tolak')
                //     ->color('danger')
                //     ->icon('heroicon-o-x-mark')
                //     ->visible(fn ($record) => $record->user && $record->user->role && $record->user->role->name === 'tamu')
                //     ->action(function ($record) {
                //         $user = $record->user;
                //         if ($user) {
                //             $user->delete(); // Atau bisa update role_id ke status lain sesuai kebutuhan
                //         }
                //     }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
