<?php

namespace App\Filament\Resources\PeriodeIurans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class PeriodeIuransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('tahun')
                    ->label('Tahun')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible(),
            ])
            ->defaultGroup('tahun')
            ->columns([
                TextColumn::make('periode_iuran_id')->label('ID')->sortable(),
                TextColumn::make('bulan')
                    ->label('Bulan')
                    ->formatStateUsing(fn ($state): string => match ((string) $state) {
                        '1' => 'Januari',
                        '2' => 'Februari',
                        '3' => 'Maret',
                        '4' => 'April',
                        '5' => 'Mei',
                        '6' => 'Juni',
                        '7' => 'Juli',
                        '8' => 'Agustus',
                        '9' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Tanggal Jatuh Tempo')
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        return \App\Models\PeriodeIuran::query()
                            ->select('tahun')
                            ->distinct()
                            ->orderByDesc('tahun')
                            ->get()
                            ->mapWithKeys(function ($row) {
                                $t = (int) $row->tahun;

                                return [$t => (string) $t];
                            })
                            ->toArray();
                    }),
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
