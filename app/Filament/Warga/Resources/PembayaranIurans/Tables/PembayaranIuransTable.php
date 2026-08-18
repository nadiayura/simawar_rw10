<?php

namespace App\Filament\Warga\Resources\PembayaranIurans\Tables;

use App\Models\TagihanIuranWarga;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PembayaranIuransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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
                    }),
                TextColumn::make('nominal_tagihan')
                    ->numeric()
                    ->label('Nominal')
                    ->sortable(),
                TextColumn::make('status.keterangan')
                    ->badge()
                    ->label('Status')
                    ->formatStateUsing(function ($state) {
                        $s = strtolower((string) $state);

                        return $s === 'settlement' ? 'Lunas' : (string) $state;
                    })
                    ->color(fn ($state): string => match (strtolower((string) $state)) {
                        'lunas' => 'success',
                        'belum bayar' => 'danger',
                        'menunggu pembayaran' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('tanggal_lunas')
                    ->date()
                    ->label('Tanggal Lunas')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('bayar_tagihan')
                    ->label('Bayar')
                    ->icon('heroicon-o-credit-card')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ! in_array(strtolower((string) ($record->status?->keterangan)), ['settlement', 'lunas'], true) && empty($record->PembayaranTunai_id))
                    ->action(function ($record) {
                        $statusName = strtolower((string) ($record->status?->keterangan));
                        if (in_array($statusName, ['lunas', 'settlement'], true)) {
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

                        return redirect()->to(route('payments.tagihan.bayar', ['tagihan' => $record->getKey(), 'panel' => 'warga']));
                    })
                    ->color('primary'),
                Action::make('lihat_bukti_tunai')
                    ->label('Lihat Bukti Tunai')
                    ->icon('heroicon-o-eye')
                    ->visible(fn ($record) => (bool) $record->PembayaranTunai_id
                        && is_array($record->pembayaranTunai?->bukti)
                        && ! empty($record->pembayaranTunai?->bukti)
                    )
                    ->modalHeading('Bukti Pembayaran Tunai')
                    ->modalSubmitAction(false)
                    ->modalWidth('xl')
                    ->modalContent(function ($record) {

                        $bukti = $record->pembayaranTunai?->bukti ?: [];

                        $html = '<div style="display:flex;justify-content:center">';
                        $html .= '<div style="width:100%;max-width:860px">';
                        $html .= '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">';

                        foreach ((array) $bukti as $path) {
                            $url = '/storage/'.ltrim((string) $path, '/');
                            $recipient = (string) ($record->pembayaranTunai?->penerima ?? 'Penerima');

                            $safeName = htmlspecialchars($recipient, ENT_QUOTES, 'UTF-8');

                            $html .= '<div style="text-align:">';
                            $html .= 'Diterima Oleh : <div style="font-size:13px;color:#374151;margin-bottom:6px;font-weight:500">'
                                .$safeName
                                .'</div>';
                            $html .= '<img src="'.$url.'" alt="'.$safeName.'" '
                                .'style="max-width:100%;max-height:420px;object-fit:contain;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.08)" />';
                            $html .= '</div>';
                        }

                        $html .= '</div></div></div>';

                        return new \Illuminate\Support\HtmlString($html);
                    }),
            ])
            ->filters([
                //
            ]);
    }
}
