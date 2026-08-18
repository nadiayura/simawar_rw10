<?php

namespace App\Filament\Resources\RekapKeuangans\Pages;

use App\Filament\Resources\RekapKeuangans\RekapKeuanganResource;
use App\Models\NoRt;
use App\Models\PembayaranMidtrans;
use App\Models\PembayaranTunai;
use App\Models\RekapKeuangan;
use App\Models\TagihanIuranWarga;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class GroupedRekapKeuangan extends Page
{
    protected static string $resource = RekapKeuanganResource::class;

    protected string $view = 'filament.resources.rekap-keuangans.pages.grouped-rekap-keuangan';

    public function getHeading(): string
    {
        return 'Rekap Keuangan';
    }

    public function getTitle(): string
    {
        return 'Rekap Keuangan';
    }

    public function getBreadcrumbs(): array
    {
        return [
            RekapKeuanganResource::getUrl('index') => 'Rekap Keuangan',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Action::make('sync_iuran')
            //     ->label('Sinkron Pemasukan dari Iuran')
            //     ->icon('heroicon-o-arrow-path')
            //     ->requiresConfirmation()
            //     ->action(function () {
            //         [$created, $skipped] = $this->syncFromIuran();

            //         Notification::make()
            //             ->title('Sinkronisasi selesai')
            //             ->body('Dibuat: '.$created.' • Dilewati: '.$skipped)
            //             ->success()
            //             ->send();
            //     }),
            Action::make('download_report')
                ->label('Unduh Laporan')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(\App\Filament\Resources\RekapKeuangans\RekapKeuanganResource::getUrl('report')),
        ];
    }

    protected function getViewData(): array
    {
        $user = Auth::user();
        $selectedRtId = request()->query('no_rt_id');
        $isRt = $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id;

        $range = request()->query('range');
        $start = request()->query('start');
        $end = request()->query('end');
        $filterStart = null;
        $filterEnd = null;
        $now = now();
        if ($range === 'month') {
            $filterStart = $now->copy()->startOfMonth();
            $filterEnd = $now->copy()->endOfMonth();
        } elseif ($range === 'week') {
            $filterStart = $now->copy()->startOfWeek();
            $filterEnd = $now->copy()->endOfWeek();
        } elseif ($range === 'year') {
            $filterStart = $now->copy()->startOfYear();
            $filterEnd = $now->copy()->endOfYear();
        } elseif ($start && $end) {
            try {
                $filterStart = Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
                $filterEnd = Carbon::createFromFormat('Y-m-d', $end)->endOfDay();
            } catch (\Throwable $e) {
                $filterStart = null;
                $filterEnd = null;
            }
        }

        $rtOptionsQuery = NoRt::query()->orderBy('nomor');
        if ($isRt) {
            $rtOptionsQuery->where('no_rt_id', $user->warga->no_rt_id);
        }
        $rtOptions = $rtOptionsQuery->pluck('nomor', 'no_rt_id')->toArray();
        if ($isRt && (! $selectedRtId || ! array_key_exists((string) $selectedRtId, $rtOptions))) {
            $selectedRtId = $user->warga->no_rt_id;
        }

        if (request()->boolean('do_sync_iuran')) {
            [$created, $skipped] = $this->syncFromIuran();

            Notification::make()
                ->title('Sinkronisasi selesai')
                ->body('Dibuat: '.$created.' • Dilewati: '.$skipped)
                ->success()
                ->send();
        }

        $unsyncedCount = TagihanIuranWarga::query()
            ->where(function ($q) {
                $q->whereNotNull('PembayaranTunai_id')
                    ->orWhereNotNull('PembayaranMidtrans_id');
            })
            ->whereNotIn('tagihan_iuran_id', RekapKeuangan::query()
                ->whereNotNull('tagihan_iuran_id')
                ->select('tagihan_iuran_id'))
            ->count();

        $year = (int) (request()->query('year', now()->year));
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $groups = [];
        foreach ($months as $m => $label) {
            $query = RekapKeuangan::query()
                ->with(['tagihan.warga'])
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $m)
                ->orderBy('tanggal');
            if ($filterStart && $filterEnd) {
                $query->whereBetween('tanggal', [$filterStart, $filterEnd]);
            }

            if ($selectedRtId) {
                $query->where(function ($q) use ($selectedRtId) {
                    $q->whereHas('tagihan.warga', function ($w) use ($selectedRtId) {
                        $w->where('no_rt_id', $selectedRtId);
                    })
                        ->orWhere(function ($qq) use ($selectedRtId) {
                            $qq->whereNull('tagihan_iuran_id')
                                ->where('no_rt_id', $selectedRtId);
                        });
                });
            } elseif ($isRt) {
                $query->where(function ($q) use ($user) {
                    $q->whereHas('tagihan.warga', function ($w) use ($user) {
                        $w->where('no_rt_id', $user->warga->no_rt_id);
                    })
                        ->orWhere(function ($qq) use ($user) {
                            $qq->whereNull('tagihan_iuran_id')
                                ->where('no_rt_id', $user->warga->no_rt_id);
                        });
                });
            }

            $rows = $query->get()->map(function (RekapKeuangan $r) {
                return [
                    'id' => $r->rekap_keuangan_id,
                    'tanggal' => optional($r->tanggal)->format('Y-m-d'),
                    'jenis' => $r->jenis,
                    'metode' => $r->metode,
                    'nominal' => (float) $r->nominal,
                    'sumber' => $r->sumber,
                    'keterangan' => $r->keterangan,
                    'payer' => optional(optional($r->tagihan)->warga)->nama,
                ];
            })->toArray();

            $totalMasuk = array_sum(array_map(function ($row) {
                return strtolower((string) $row['jenis']) === 'masuk' ? (float) $row['nominal'] : 0.0;
            }, $rows));
            $totalKeluar = array_sum(array_map(function ($row) {
                return strtolower((string) $row['jenis']) === 'keluar' ? (float) $row['nominal'] : 0.0;
            }, $rows));

            $groups[] = [
                'bulan_label' => $label.' '.$year,
                'rows' => $rows,
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
            ];
        }

        $overallMasuk = array_sum(array_map(fn ($g) => (float) ($g['total_masuk'] ?? 0), $groups));
        $overallKeluar = array_sum(array_map(fn ($g) => (float) ($g['total_keluar'] ?? 0), $groups));

        $queryAll = RekapKeuangan::query()
            ->with(['tagihan.warga'])
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal');
        if ($filterStart && $filterEnd) {
            $queryAll->whereBetween('tanggal', [$filterStart, $filterEnd]);
        }
        if ($selectedRtId) {
            $queryAll->where(function ($q) use ($selectedRtId) {
                $q->whereHas('tagihan.warga', function ($w) use ($selectedRtId) {
                    $w->where('no_rt_id', $selectedRtId);
                })
                    ->orWhere(function ($qq) use ($selectedRtId) {
                        $qq->whereNull('tagihan_iuran_id')
                            ->where('no_rt_id', $selectedRtId);
                    });
            });
        } elseif ($isRt) {
            $queryAll->where(function ($q) use ($user) {
                $q->whereHas('tagihan.warga', function ($w) use ($user) {
                    $w->where('no_rt_id', $user->warga->no_rt_id);
                })
                    ->orWhere(function ($qq) use ($user) {
                        $qq->whereNull('tagihan_iuran_id')
                            ->where('no_rt_id', $user->warga->no_rt_id);
                    });
            });
        }
        $allRows = $queryAll->get()->map(function (RekapKeuangan $r) {
            return [
                'id' => $r->rekap_keuangan_id,
                'tanggal' => optional($r->tanggal)->format('Y-m-d'),
                'jenis' => $r->jenis,
                'metode' => $r->metode,
                'nominal' => (float) $r->nominal,
                'sumber' => $r->sumber,
                'keterangan' => $r->keterangan,
                'payer' => optional(optional($r->tagihan)->warga)->nama,
            ];
        })->toArray();
        $masukRows = array_values(array_filter($allRows, fn ($row) => strtolower((string) ($row['jenis'] ?? '')) === 'masuk'));
        $keluarRows = array_values(array_filter($allRows, fn ($row) => strtolower((string) ($row['jenis'] ?? '')) === 'keluar'));
        $masukTotal = array_sum(array_map(fn ($row) => (float) ($row['nominal'] ?? 0), $masukRows));
        $keluarTotal = array_sum(array_map(fn ($row) => (float) ($row['nominal'] ?? 0), $keluarRows));
        $masukBySumber = [];
        foreach ($masukRows as $r) {
            $s = (string) ($r['sumber'] ?? '');
            $masukBySumber[$s] = ($masukBySumber[$s] ?? 0) + (float) ($r['nominal'] ?? 0);
        }
        $keluarBySumber = [];
        foreach ($keluarRows as $r) {
            $s = (string) ($r['sumber'] ?? '');
            $keluarBySumber[$s] = ($keluarBySumber[$s] ?? 0) + (float) ($r['nominal'] ?? 0);
        }

        return [
            'groups' => $groups,
            'rtOptions' => $rtOptions,
            'selectedRtId' => $selectedRtId,
            'range' => $range,
            'start' => $start,
            'end' => $end,
            'year' => $year,
            'overall_masuk' => $overallMasuk,
            'overall_keluar' => $overallKeluar,
            'masuk_rows' => $masukRows,
            'keluar_rows' => $keluarRows,
            'masuk_total' => $masukTotal,
            'keluar_total' => $keluarTotal,
            'masuk_by_sumber' => $masukBySumber,
            'keluar_by_sumber' => $keluarBySumber,
            'needs_sync' => $unsyncedCount > 0,
            'unsynced_count' => $unsyncedCount,
        ];
    }

    public function deleteTransaksi(string $id): void
    {
        $record = RekapKeuangan::query()->findOrFail($id);

        if (! RekapKeuanganResource::canDelete($record)) {
            abort(403);
        }

        $record->delete();

        Notification::make()
            ->title('Transaksi berhasil dihapus')
            ->success()
            ->send();
    }

    protected function syncFromIuran(): array
    {
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

        return [$created, $skipped];
    }
}
