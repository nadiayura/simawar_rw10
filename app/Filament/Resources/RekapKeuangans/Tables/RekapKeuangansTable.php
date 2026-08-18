<?php

namespace App\Filament\Resources\RekapKeuangans\Tables;

use App\Filament\Resources\RekapKeuangans\RekapKeuanganResource;
use App\Models\PembayaranMidtrans;
use App\Models\PembayaranTunai;
use App\Models\RekapKeuangan;
use App\Models\TagihanIuranWarga;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class RekapKeuangansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'masuk' => 'success',
                        'keluar' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('metode')
                    ->sortable(),
                TextColumn::make('tagihan.warga.nama')
                    ->label('Nama Warga')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sumber'),
                TextColumn::make('nominal')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((float) $state, 2, ',', '.'))
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('bukti')
                    ->label('Bukti')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return count($state).' file';
                        }

                        return $state ? '1 file' : '-';
                    })
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
                EditAction::make()
                    ->url(fn (RekapKeuangan $record) => RekapKeuanganResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([
                Action::make('sync_iuran')
                    ->label('Sinkron Pemasukan dari Iuran')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function () {
                        $tagihans = TagihanIuranWarga::query()
                            ->where(function ($q) {
                                $q->whereNotNull('PembayaranTunai_id')
                                    ->orWhereNotNull('PembayaranMidtrans_id');
                            })
                            ->get();

                        $created = 0;
                        $skipped = 0;

                        foreach ($tagihans as $t) {
                            $hasPmCol = Schema::hasColumn('rekap_keuangan', 'PembayaranMidtrans_id');
                            $exists = RekapKeuangan::query()
                                ->where('tagihan_iuran_id', $t->tagihan_iuran_id)
                                ->exists();
                            if ($exists) {
                                $skipped++;

                                continue;
                            }

                            $tanggal = $t->tanggal_lunas ?? now();
                            $payload = [
                                'tanggal' => $tanggal,
                                'jenis' => 'masuk',
                                'sumber' => 'iuran',
                                'nominal' => (float) $t->nominal_tagihan,
                                'bukti' => [],
                                'metode' => 'midtrans',
                                'tagihan_iuran_id' => $t->tagihan_iuran_id,
                            ];

                            if ($t->PembayaranTunai_id) {
                                $ptunai = PembayaranTunai::find($t->PembayaranTunai_id);
                                if ($ptunai) {
                                    $payload['nominal'] = (float) $ptunai->nominal_dibayarkan;
                                    $payload['bukti'] = is_array($ptunai->bukti) ? $ptunai->bukti : [];
                                    $payload['metode'] = 'tunai';
                                    $tanggal = $t->tanggal_lunas ?? $ptunai->created_at ?? $tanggal;
                                    $payload['tanggal'] = $tanggal;
                                } else {
                                    $payload['metode'] = 'tunai';
                                }
                            } elseif ($t->PembayaranMidtrans_id) {
                                $pm = PembayaranMidtrans::find($t->PembayaranMidtrans_id);
                                if ($pm) {
                                    $pt = strtolower((string) $pm->tipe_pembayaran);
                                    $metode = (str_contains($pt, 'va') || str_contains($pt, 'bank') || str_contains($pt, 'transfer')) ? 'transfer' : 'midtrans';
                                    $payload['metode'] = $metode;
                                    $payload['nominal'] = (float) $pm->jumlah;
                                    $tanggal = $t->tanggal_lunas ?? $pm->updated_at ?? $tanggal;
                                    $payload['tanggal'] = $tanggal;
                                    if ($hasPmCol) {
                                        $payload['PembayaranMidtrans_id'] = $pm->PembayaranMidtrans_id;
                                    }
                                }
                            }

                            RekapKeuangan::create($payload);
                            $created++;
                        }

                        Notification::make()
                            ->title('Sinkronisasi selesai')
                            ->body('Dibuat: '.$created.' • Dilewati: '.$skipped)
                            ->success()
                            ->send();
                    }),
                Action::make('download_report')
                    ->label('Unduh Laporan')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(RekapKeuanganResource::getUrl('report')),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
