<?php

namespace App\Filament\Resources\Strukturals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StrukturalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('warga.nama')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('jabatan')
                    ->searchable(),
                TextColumn::make('periode')
                    ->label('Periode')
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
