<?php

namespace App\Filament\Resources\TagihanIuranWargas\Tables;

use App\Models\TagihanIuranWarga;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagihanIuranWargasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('warga.nama')
                    ->label('Warga')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('periode.bulan')
                    ->label('Bulan')
                    ->formatStateUsing(function ($state) {
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

                        return $map[(int) $state] ?? (string) $state;
                    })
                    ->sortable(),
                TextColumn::make('nominal_tagihan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status.keterangan')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        $s = strtolower((string) $state);

                        return in_array($s, ['lunas', 'settlement'], true) ? 'Lunas' : (string) $state;
                    })
                    ->color(fn ($state): string => match (strtolower((string) $state)) {
                        'lunas' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('tanggal_lunas')
                    ->date()
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
                EditAction::make(),
                Action::make('bayar_tagihan')
                    ->label('Bayar Tagihan')
                    ->icon('heroicon-o-credit-card')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $statusName = strtolower((string) ($record->status?->keterangan));
                        if (in_array($statusName, ['lunas'], true)) {
                            Notification::make()
                                ->title('Tagihan bulan ini sudah dibayar')
                                ->success()
                                ->send();

                            return;
                        }
                        $unpaidIds = array_values(array_filter([
                            \App\Models\Status::idForFitur('keuangan', 'Belum bayar'),
                        ]));
                        $currentPeriode = \Illuminate\Support\Facades\DB::table('periode_iurans')
                            ->where('periode_iuran_id', $record->periode_id)
                            ->first();
                        if ($currentPeriode) {
                            $earliestUnpaid = TagihanIuranWarga::query()
                                ->where('warga_nik', $record->warga_nik)
                                ->where('iuran_id', $record->iuran_id)
                                ->whereIn('status_id', $unpaidIds)
                                ->join('periode_iurans', 'periode_iurans.periode_iuran_id', '=', 'tagihan_iuran_wargas.periode_id')
                                ->where('periode_iurans.tahun', (int) $currentPeriode->tahun)
                                ->orderBy('periode_iurans.bulan')
                                ->select('tagihan_iuran_wargas.periode_id')
                                ->first();

                            if ($earliestUnpaid && (string) $record->periode_id !== (string) $earliestUnpaid->periode_id) {
                                Notification::make()
                                    ->title('Silahkan lakukan pembayaran pada bulan sebelumnya')
                                    ->danger()
                                    ->send();

                                return;
                            }
                        }

                        return redirect()->to(route('payments.tagihan.bayar', ['tagihan' => $record->getKey(), 'panel' => 'admin']));
                    })
                    ->disabled(fn ($record) => in_array(strtolower((string) ($record->status?->keterangan)), ['lunas', 'settlement'], true))
                    ->color(fn ($record) => in_array(strtolower((string) ($record->status?->keterangan)), ['lunas', 'settlement'], true) ? 'success' : 'primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
