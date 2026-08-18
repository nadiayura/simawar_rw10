<?php

namespace App\Filament\Warga\Widgets;

use App\Filament\Warga\Resources\DataWargaResource;
use App\Filament\Warga\Resources\PembayaranIurans\PembayaranIuranResource;
use App\Models\KegKarangTaruna;
use App\Models\KegKesehatan;
use App\Models\KetuaRt;
use App\Models\NoRt;
use App\Models\Pengaduan;
use App\Models\PeriodeIuran;
use App\Models\RekapKeuangan;
use App\Models\Status;
use App\Models\SuratKetWarga;
use App\Models\TagihanIuranWarga;
use App\Models\Warga;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WargaOverviewWidget extends Widget
{
    protected string $view = 'filament.warga.widgets.overview';

    protected int|string|array $columnSpan = 'full';

    public int $rekapSelectedYear;

    public int $rekapSelectedMonth;

    public function updateRekapPeriod(): void {}

    public function mount(): void
    {
        $this->rekapSelectedYear = (int) (request()->query('rekap_year', now()->year));
        $this->rekapSelectedMonth = (int) (request()->query('rekap_month', now()->month));
    }

    protected function getViewData(): array
    {
        $user = Auth::user();
        $wargaNik = $user?->warga_nik;
        $isTamu = $user && $user->role && ($user->role->isTamu() || (int) $user->role_id === 8);
        $isVerified = $user && $user->role && $user->role->isWarga();
        $canShowDashboard = (bool) ($isVerified && $wargaNik);

        $nama = $wargaNik ? Warga::query()->where('warga_nik', $wargaNik)->value('nama') : ($user?->name);

        $showActions = (bool) $canShowDashboard;
        $needsDataDiri = $isTamu && ! $wargaNik;
        $verificationPending = $isTamu && (bool) $wargaNik;
        $dataDiriCreateUrl = DataWargaResource::getUrl('create');
        $dataDiriViewUrl = $wargaNik ? DataWargaResource::getUrl('view', ['record' => $wargaNik]) : null;

        if (! $canShowDashboard) {
            return [
                'nama' => $nama,
                'showActions' => $showActions,
                'needsDataDiri' => $needsDataDiri,
                'verificationPending' => $verificationPending,
                'dataDiriCreateUrl' => $dataDiriCreateUrl,
                'dataDiriViewUrl' => $dataDiriViewUrl,
                'canShowDashboard' => false,
            ];
        }

        $currentPeriod = PeriodeIuran::query()
            ->where('tahun', now()->year)
            ->where('bulan', now()->month)
            ->first();

        $statusIuran = null;
        if ($wargaNik && $currentPeriod) {
            $tagihan = TagihanIuranWarga::query()
                ->where('warga_nik', $wargaNik)
                ->where('periode_iuran_id', $currentPeriod->periode_iuran_id)
                ->first();
            $statusIuran = $tagihan?->status?->keterangan;
        }

        $bulanNama = match ((string) now()->month) {
            '1' => 'Januari','2' => 'Februari','3' => 'Maret','4' => 'April','5' => 'Mei','6' => 'Juni','7' => 'Juli','8' => 'Agustus','9' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember',
            default => ''
        };

        $iuranValue = match (strtolower((string) $statusIuran)) {
            'lunas' => 'Lunas',
            'menunggu pembayaran' => 'Menunggu',
            default => 'Belum bayar',
        };
        $iuranDesc = $currentPeriod
            ? ('Periode: '.$bulanNama.' '.$currentPeriod->tahun)
            : 'Periode belum tersedia.';

        if ($currentPeriod && $wargaNik) {
            $shortMonths = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
            $year = $currentPeriod->tahun;
            $endMonth = (int) $currentPeriod->bulan;
            $startMonth = $endMonth;
            for ($m = $endMonth; $m >= 1; $m--) {
                $p = PeriodeIuran::query()->where('tahun', $year)->where('bulan', $m)->first();
                if (! $p) {
                    continue;
                }
                $t = TagihanIuranWarga::query()->where('warga_nik', $wargaNik)->where('periode_iuran_id', $p->periode_iuran_id)->first();
                if ($t && strtolower((string) $t->status?->keterangan) === 'lunas') {
                    break;
                }
                $startMonth = $m;
            }
            if ($iuranValue === 'Lunas') {
                $allPaid = true;
                for ($m = 1; $m <= $endMonth; $m++) {
                    $p = PeriodeIuran::query()->where('tahun', $year)->where('bulan', $m)->first();
                    if (! $p) {
                        continue;
                    }
                    $t = TagihanIuranWarga::query()->where('warga_nik', $wargaNik)->where('periode_iuran_id', $p->periode_iuran_id)->first();
                    if (! $t || strtolower((string) $t->status?->keterangan) !== 'lunas') {
                        $allPaid = false;
                        break;
                    }
                }
                if ($allPaid) {
                    $iuranDesc = 'Lunas semua pembayaran.';
                }
            } else {
                if (isset($shortMonths[$startMonth]) && isset($shortMonths[$endMonth])) {
                    $rangeText = $startMonth === $endMonth
                        ? ($shortMonths[$endMonth].' '.$year)
                        : ($shortMonths[$startMonth].' - '.$shortMonths[$endMonth].' '.$year);

                    $iuranDesc = 'Periode: '.$rangeText;
                }
            }
        }
        $iuranColor = match (strtolower((string) $statusIuran)) {
            'lunas' => 'success',
            'menunggu pembayaran' => 'warning',
            default => 'danger',
        };

        $selectedRtId = optional($user?->warga)->no_rt_id ?? -1;
        $rtLabel = null;
        if ($selectedRtId && $selectedRtId !== -1) {
            $rt = NoRt::find($selectedRtId);
            if ($rt && $rt->nomor !== null) {
                $rtLabel = 'RT '.str_pad((string) $rt->nomor, 3, '0', STR_PAD_LEFT);
            }
        }

        $rekapSelectedYear = $this->rekapSelectedYear ?: now()->year;
        $rekapSelectedMonth = $this->rekapSelectedMonth ?: now()->month;
        $rekapMonthName = match ((string) $rekapSelectedMonth) {
            '1' => 'Januari','2' => 'Februari','3' => 'Maret','4' => 'April','5' => 'Mei','6' => 'Juni','7' => 'Juli','8' => 'Agustus','9' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember',
            default => ''
        };
        $rekapQuery = RekapKeuangan::query()
            ->with(['tagihan.warga'])
            ->whereYear('tanggal', $rekapSelectedYear)
            ->whereMonth('tanggal', $rekapSelectedMonth)
            ->orderBy('tanggal');
        $rekapQuery->where(function ($q) use ($selectedRtId) {
            $q->whereHas('tagihan.warga', function ($w) use ($selectedRtId) {
                $w->where('no_rt_id', $selectedRtId);
            })
                ->orWhere(function ($qq) use ($selectedRtId) {
                    $qq->whereNull('tagihan_iuran_id')
                        ->where('no_rt_id', $selectedRtId);
                });
        });
        $rekapRows = $rekapQuery->get()->map(function (RekapKeuangan $r) {
            return [
                'jenis' => $r->jenis,
                'nominal' => (float) $r->nominal,
            ];
        })->toArray();
        $rekapMasuk = array_sum(array_map(fn ($row) => strtolower((string) ($row['jenis'] ?? '')) === 'masuk' ? (float) ($row['nominal'] ?? 0) : 0, $rekapRows));
        $rekapKeluar = array_sum(array_map(fn ($row) => strtolower((string) ($row['jenis'] ?? '')) === 'keluar' ? (float) ($row['nominal'] ?? 0) : 0, $rekapRows));
        $rekapSaldo = $rekapMasuk - $rekapKeluar;
        $rekapTotal = $rekapMasuk + $rekapKeluar;
        $percentMasuk = $rekapTotal > 0 ? round(($rekapMasuk / $rekapTotal) * 100) : 0;
        $percentKeluar = $rekapTotal > 0 ? (100 - $percentMasuk) : 0;

        $trendLabels = [];
        $trendMasuk = [];
        $trendKeluar = [];
        $monthsCount = 6;
        $startMonthDate = now()->startOfMonth()->subMonths($monthsCount - 1);
        for ($i = 0; $i < $monthsCount; $i++) {
            $monthDate = (clone $startMonthDate)->addMonths($i);
            $m = (int) $monthDate->month;
            $y = (int) $monthDate->year;
            $label = match ((string) $m) {
                '1' => 'Jan','2' => 'Feb','3' => 'Mar','4' => 'Apr','5' => 'Mei','6' => 'Jun','7' => 'Jul','8' => 'Agu','9' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des',
                default => (string) $m,
            };
            $query = RekapKeuangan::query()
                ->with(['tagihan.warga'])
                ->whereYear('tanggal', $y)
                ->whereMonth('tanggal', $m);
            $query->where(function ($q) use ($selectedRtId) {
                $q->whereHas('tagihan.warga', function ($w) use ($selectedRtId) {
                    $w->where('no_rt_id', $selectedRtId);
                })
                    ->orWhere(function ($qq) use ($selectedRtId) {
                        $qq->whereNull('tagihan_iuran_id')
                            ->where('no_rt_id', $selectedRtId);
                    });
            });
            $rows = $query->get()->map(function (RekapKeuangan $r) {
                return [
                    'jenis' => $r->jenis,
                    'nominal' => (float) $r->nominal,
                ];
            })->toArray();
            $masuk = array_sum(array_map(fn ($row) => strtolower((string) ($row['jenis'] ?? '')) === 'masuk' ? (float) ($row['nominal'] ?? 0) : 0, $rows));
            $keluar = array_sum(array_map(fn ($row) => strtolower((string) ($row['jenis'] ?? '')) === 'keluar' ? (float) ($row['nominal'] ?? 0) : 0, $rows));
            $trendLabels[] = $label;
            $trendMasuk[] = $masuk;
            $trendKeluar[] = $keluar;
        }
        $trendMax = max(array_merge($trendMasuk, $trendKeluar, [0]));

        $pengaduan = $wargaNik ? Pengaduan::query()->where('warga_nik', $wargaNik)->latest('tgl_pengajuan')->first() : null;
        $pengaduanValue = $pengaduan ? ('#'.$pengaduan->id) : null;
        $pengaduanTitleRaw = $pengaduan ? trim((string) ($pengaduan->jdl_pengaduan ?? '')) : '';
        $pengaduanTitle = $pengaduan
            ? ($pengaduanTitleRaw !== '' ? Str::limit($pengaduanTitleRaw, 40) : ('Pengaduan #'.$pengaduan->id))
            : null;
        $pengaduanStatus = $pengaduan ? ($pengaduan->status?->keterangan ?? '-') : null;
        $pengaduanDesc = $pengaduan ? (Str::limit($pengaduan->jdl_pengaduan ?? '', 40).' • Status: '.($pengaduan->status?->keterangan ?? '-')) : null;

        $surat = $wargaNik ? SuratKetWarga::query()->with('jenisSurat')->where('warga_nik', $wargaNik)->latest('created_at')->first() : null;
        $suratValue = $surat ? ($surat->status?->keterangan ?? '-') : null;
        $suratDesc = $surat ? ($surat->jenisSurat?->nama_surat ?? '-') : null;
        $suratTanggalPengajuan = $surat?->created_at;

        $kegiatanItems = [];

        $karangTarunaQuery = KegKarangTaruna::query()
            ->where('tanggal', '>=', now())
            ->orderBy('tanggal')
            ->limit(5);

        if ($selectedRtId && $selectedRtId !== -1) {
            $karangTarunaQuery->whereHas('pjWarga', function ($q) use ($selectedRtId) {
                $q->where('no_rt_id', $selectedRtId);
            });
        } else {
            $karangTarunaQuery->whereRaw('1 = 0');
        }

        $karangTaruna = $karangTarunaQuery->get();

        foreach ($karangTaruna as $k) {
            if (! $k->tanggal) {
                continue;
            }
            $kegiatanItems[] = [
                'at' => $k->tanggal,
                'nama' => 'Karang Taruna - '.$k->nama_kegiatan,
                'waktu' => optional($k->tanggal)->translatedFormat('l, d F Y, H.i'),
            ];
        }

        $statusDijadwalkanId = Status::idForFitur('keg_warga', 'Dijadwalkan')
            ?? Status::idByName('Dijadwalkan');

        if ($statusDijadwalkanId) {
            $kesehatan = KegKesehatan::query()
                ->with('status')
                ->where('status_id', $statusDijadwalkanId)
                ->whereDate('tgl', '>=', now()->toDateString())
                ->orderBy('tgl')
                ->limit(5)
                ->get();

            foreach ($kesehatan as $k) {
                if (! $k->tgl) {
                    continue;
                }
                $kegiatanItems[] = [
                    'at' => $k->tgl->copy()->startOfDay(),
                    'nama' => 'Kesehatan - '.$k->nama_kegiatan,
                    'waktu' => optional($k->tgl)->translatedFormat('l, d F Y'),
                ];
            }
        }

        usort($kegiatanItems, function (array $a, array $b) {
            $aAt = $a['at'] ?? null;
            $bAt = $b['at'] ?? null;

            $aTs = $aAt instanceof \Carbon\CarbonInterface ? $aAt->getTimestamp() : 0;
            $bTs = $bAt instanceof \Carbon\CarbonInterface ? $bAt->getTimestamp() : 0;

            return $aTs <=> $bTs;
        });

        $kegiatanItems = array_values(array_slice($kegiatanItems, 0, 3));
        $kegiatanValue = $kegiatanItems[0]['nama'] ?? null;
        $kegiatanDesc = $kegiatanItems[0]['waktu'] ?? null;

        // Tracking layanan saya (pengaduan & surat)
        $tracking = [];
        if ($wargaNik) {
            $pengaduans = Pengaduan::query()
                ->where('warga_nik', $wargaNik)
                ->orderByDesc('tgl_pengajuan')
                ->limit(5)
                ->get();
            foreach ($pengaduans as $p) {
                $label = match ($p->status?->keterangan) {
                    'pending' => 'Menunggu',
                    'diproses' => 'Diproses',
                    'selesai' => 'Selesai',
                    'ditolak' => 'Ditolak',
                    default => ucfirst((string) $p->status?->keterangan),
                };
                $badge = match ($label) {
                    'Selesai' => 'green',
                    'Menunggu' => 'yellow',
                    'Diproses' => 'blue',
                    'Disetujui' => 'green',
                    'Ditolak' => 'red',
                    default => 'gray',
                };
                $tracking[] = [
                    'fitur' => 'Pengaduan',
                    'jenis' => (trim((string) ($p->jdl_pengaduan ?? '')) !== '')
                        ? ('Pengaduan '.$p->id.Str::limit(trim((string) ($p->jdl_pengaduan ?? '')), 40))
                        : ('Pengaduan'.$p->id),
                    'tanggal' => $p->tgl_pengajuan,
                    'label' => $label,
                    'badge' => $badge,
                ];
            }

            $surats = SuratKetWarga::query()
                ->with('jenisSurat')
                ->where('warga_nik', $wargaNik)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
            foreach ($surats as $s) {
                $label = match ($s->status?->keterangan) {
                    'Diajukan' => 'Diajukan',
                    'Menunggu Verifikasi' => 'Menunggu Verifikasi',
                    'Selesai' => 'Selesai',
                    'Ditolak' => 'Ditolak',

                    default => (string) $s->status?->keterangan,
                };
                $badge = match ($label) {
                    'Selesai' => 'green',
                    'Menunggu Verifikasi' => 'yellow',
                    'Ditolak' => 'red',
                    'Diajukan' => 'blue',
                    'Disetujui' => 'green',
                    default => 'gray',
                };
                $tracking[] = [
                    'fitur' => 'Persuratan',
                    'jenis' => ($s->jenisSurat?->nama_surat ?? 'Keterangan'),
                    'tanggal' => $s->created_at,
                    'label' => $label,
                    'badge' => $badge,
                ];
            }

            usort($tracking, function ($a, $b) {
                $ta = $a['tanggal'] ? $a['tanggal']->getTimestamp() : 0;
                $tb = $b['tanggal'] ? $b['tanggal']->getTimestamp() : 0;

                return $tb <=> $ta;
            });
        }

        $trackingByFitur = [];
        $allowedFitur = ['Pengaduan', 'Persuratan']; // Urutan tab yang diinginkan

        foreach ($allowedFitur as $f) {
            $trackingByFitur[$f] = [];
        }

        foreach ($tracking as $row) {
            $fitur = $row['fitur'] ?? 'Lainnya';
            if (! isset($trackingByFitur[$fitur])) {
                $trackingByFitur[$fitur] = [];
            }
            $trackingByFitur[$fitur][] = $row;
        }

        $fiturLabels = array_keys($trackingByFitur);
        $activeFitur = $fiturLabels[0] ?? null;

        $emergencyContacts = [];
        if ($selectedRtId && $selectedRtId !== -1) {
            $ketuas = KetuaRt::query()
                ->where('no_rt_id', $selectedRtId)
                ->active()
                ->with('warga')
                ->orderBy('jabatan')
                ->limit(4)
                ->get();

            foreach ($ketuas as $k) {
                $rawPhone = (string) $k->no_hp;
                $digits = preg_replace('/\D+/', '', $rawPhone ?? '');
                if ($digits !== '') {
                    if (str_starts_with($digits, '0')) {
                        $digits = '62'.substr($digits, 1);
                    } elseif (str_starts_with($digits, '62')) {
                    } elseif (str_starts_with($digits, '8')) {
                        $digits = '62'.$digits;
                    }
                }
                $waUrl = $digits !== '' ? ('https://wa.me/'.$digits) : null;

                $emergencyContacts[] = [
                    'jabatan' => $k->jabatan,
                    'nama' => optional($k->warga)->nama,
                    'no_hp' => $k->no_hp,
                    'wa_url' => $waUrl,
                ];
            }
        }

        $verificationPending = false;

        $rekapMonthOptions = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        $yearNow = now()->year;
        $rekapYearOptions = [];
        for ($y = $yearNow - 1; $y <= $yearNow + 1; $y++) {
            $rekapYearOptions[] = $y;
        }

        return [
            'nama' => $nama,
            'bulanNama' => $bulanNama,
            'iuranValue' => $iuranValue,
            'iuranDesc' => $iuranDesc,
            'iuranColor' => $iuranColor,
            'pengaduanValue' => $pengaduanValue,
            'pengaduanTitle' => $pengaduanTitle,
            'pengaduanStatus' => $pengaduanStatus,
            'pengaduanDesc' => $pengaduanDesc,
            'suratValue' => $suratValue,
            'suratDesc' => $suratDesc,
            'suratTanggalPengajuan' => $suratTanggalPengajuan,
            'kegiatanValue' => $kegiatanValue,
            'kegiatanDesc' => $kegiatanDesc,
            'kegiatanItems' => $kegiatanItems,
            'tracking' => $tracking,
            'trackingByFitur' => $trackingByFitur,
            'trackingFiturLabels' => $fiturLabels,
            'trackingActiveFitur' => $activeFitur,
            'showActions' => $showActions,
            'needsDataDiri' => $needsDataDiri,
            'verificationPending' => $verificationPending,
            'dataDiriCreateUrl' => $dataDiriCreateUrl,
            'dataDiriViewUrl' => $dataDiriViewUrl,
            'canShowDashboard' => true,
            'rekapData' => [
                'total_masuk' => $rekapMasuk,
                'total_keluar' => $rekapKeluar,
                'saldo_akhir' => $rekapSaldo,
            ],
            'rekapPercent' => [
                'masuk' => $percentMasuk,
                'keluar' => $percentKeluar,
            ],
            'rekapPeriodeLabel' => 'Periode: '.$rekapMonthName.' '.$rekapSelectedYear,
            'rekapPageUrl' => PembayaranIuranResource::getUrl(),
            'emergencyContacts' => $emergencyContacts,
            'rekapMonthOptions' => $rekapMonthOptions,
            'rekapSelectedMonth' => $rekapSelectedMonth,
            'rekapYearOptions' => $rekapYearOptions,
            'rekapSelectedYear' => $rekapSelectedYear,
            'rtLabel' => $rtLabel,
            'trendLabels' => $trendLabels,
            'trendMasuk' => $trendMasuk,
            'trendKeluar' => $trendKeluar,
            'trendMax' => $trendMax,
        ];
    }
}
