<?php

use App\Http\Controllers\FonnteController;
use App\Http\Controllers\KegKarangTarunaController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WargaPasswordResetController;
use App\Http\Controllers\WargaVerificationController;
use App\Http\Controllers\WelcomeController;
use App\Models\RekapKeuangan;
use App\Models\SuratKetWarga;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as VerifyCsrfTokenMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/galeri', [KegKarangTarunaController::class, 'index'])->name('galeri');
Route::get('/galeri/{keg_karang_taruna_id}', [KegKarangTarunaController::class, 'show'])->name('galeri.detail');
Route::get('/berita-kesehatan/{keg_kesehatan_id}', [WelcomeController::class, 'showBeritaKesehatan'])->name('berita-kesehatan.detail');
Route::get('/kegiatan-kesehatan', [WelcomeController::class, 'kegiatanKesehatan'])->name('kegiatan-kesehatan');

// Payments
Route::get('/bayar/tagihan/{tagihan}', [PaymentController::class, 'bayarTagihan'])
    ->name('payments.tagihan.bayar');
Route::post('/midtrans/notification', [PaymentController::class, 'notification'])
    ->name('payments.midtrans.notification')
    ->withoutMiddleware([VerifyCsrfTokenMiddleware::class]);
Route::get('/midtrans/update', [PaymentController::class, 'updateStatus'])
    ->name('payments.midtrans.update');
Route::post('/bayar/tunai/{tagihan}', [PaymentController::class, 'bayarTunai'])
    ->name('payments.tagihan.tunai');
Route::get('/bayar/tunai/{tagihan}/preview', [PaymentController::class, 'prepareTunai'])
    ->name('payments.tagihan.tunai.preview');

// Fonnte QR display
Route::get('/fonnte/qr/{device}', [FonnteController::class, 'qr'])
    ->name('fonnte.qr');

// Dokumen pendukung surat - viewer
Route::get('/surat/dokumen/{surat}', function (SuratKetWarga $surat) {
    return view('surat-dokumen', compact('surat'));
})->name('surat.dokumen');

// Download laporan keuangan (admin)
Route::get('/admin/rekap-keuangan/download', function (Request $request) {
    $year = (int) ($request->query('year', now()->year));
    $start = (int) ($request->query('start_month', 1));
    $end = (int) ($request->query('end_month', $start));
    if ($end < $start) {
        [$start, $end] = [$end, $start];
    }

    $rows = RekapKeuangan::query()
        ->whereYear('tanggal', $year)
        ->whereRaw('MONTH(tanggal) BETWEEN ? AND ?', [$start, $end])
        ->orderBy('tanggal')
        ->get(['tanggal', 'jenis_trans', 'sumber', 'nominal', 'metode', 'PembayaranMidtrans_id']);

    $csv = fopen('php://temp', 'w');
    fputcsv($csv, ['Tanggal', 'Jenis', 'Sumber', 'Nominal', 'Metode', 'Payment Transaction ID']);
    foreach ($rows as $r) {
        fputcsv($csv, [
            optional($r->tanggal)->format('Y-m-d'),
            $r->jenis,
            $r->sumber,
            (string) $r->nominal,
            $r->metode,
            $r->PembayaranMidtrans_id,
        ]);
    }
    rewind($csv);
    $filename = sprintf('laporan-keuangan_%d_%02d-%02d.csv', $year, $start, $end);

    return response()->streamDownload(function () use ($csv) {
        fpassthru($csv);
    }, $filename, [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
})->name('admin.rekap-keuangan.download');

Route::get('/admin/rekap-keuangan/download-range', function (Request $request) {
    $start = $request->query('start');
    $end = $request->query('end');
    $category = $request->query('category');
    $noRtId = $request->query('no_rt_id');
    $user = auth()->user();
    if ($user && $user->role && $user->role->isRT()) {
        $noRtId = optional($user->warga)->no_rt_id;
    }

    $query = RekapKeuangan::query()->orderBy('tanggal')->whereIn('jenis_trans', ['masuk', 'keluar']);
    try {
        if ($start && $end) {
            $s = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
            $e = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $end)->endOfDay();
            $query->whereBetween('tanggal', [$s, $e]);
        }
    } catch (\Throwable $e) {
        // ignore invalid date
    }
    if ($category) {
        $query->where(function ($q) use ($category) {
            $q->where('jenis_trans', 'keluar')
                ->orWhere(function ($qq) use ($category) {
                    $qq->where('jenis_trans', 'masuk')
                        ->where('sumber', $category);
                });
        });
    }
    if ($noRtId) {
        $query->where(function ($q) use ($noRtId) {
            $q->whereHas('tagihan.warga', function ($w) use ($noRtId) {
                $w->where('no_rt_id', $noRtId);
            })
                ->orWhere(function ($qq) use ($noRtId) {
                    $qq->whereNull('tagihan_iuran_id')
                        ->where('no_rt_id', $noRtId);
                });
        });
    }

    $rows = $query->get(['tanggal', 'jenis_trans', 'sumber', 'nominal', 'metode', 'PembayaranMidtrans_id']);
    $csv = fopen('php://temp', 'w');
    fputcsv($csv, ['Tanggal', 'Jenis', 'Sumber', 'Nominal', 'Metode', 'Payment Transaction ID']);
    foreach ($rows as $r) {
        fputcsv($csv, [
            optional($r->tanggal)->format('Y-m-d'),
            $r->jenis,
            $r->sumber,
            (string) $r->nominal,
            $r->metode,
            $r->PembayaranMidtrans_id,
        ]);
    }
    rewind($csv);
    $filename = sprintf('laporan-keuangan_%s_%s%s.csv', $start ?: 'all', $end ?: 'all', $noRtId ? ('_'.$noRtId) : '');

    return response()->streamDownload(function () use ($csv) {
        fpassthru($csv);
    }, $filename, [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
})->name('admin.rekap-keuangan.download-range');

Route::get('/admin/rekap-keuangan/download-pdf', function (Request $request) {
    $start = $request->query('start');
    $end = $request->query('end');
    $category = $request->query('category');
    $noRtId = $request->query('no_rt_id');
    $user = auth()->user();
    if ($user && $user->role && $user->role->isRT()) {
        $noRtId = optional($user->warga)->no_rt_id;
    }

    $query = RekapKeuangan::query()->with(['tagihan.warga', 'tagihan.periode'])->orderBy('tanggal');
    try {
        if ($start && $end) {
            $s = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
            $e = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $end)->endOfDay();
            $query->whereBetween('tanggal', [$s, $e]);
        }
    } catch (\Throwable $e) {
    }
    if ($category) {
        $query->where(function ($q) use ($category) {
            $q->where('jenis_trans', 'keluar')
                ->orWhere(function ($qq) use ($category) {
                    $qq->where('jenis_trans', 'masuk')
                        ->where('sumber', $category);
                });
        });
    }
    if ($noRtId) {
        $query->where(function ($q) use ($noRtId) {
            $q->whereHas('tagihan.warga', function ($w) use ($noRtId) {
                $w->where('no_rt_id', $noRtId);
            })
                ->orWhere(function ($qq) use ($noRtId) {
                    $qq->whereNull('tagihan_iuran_id')
                        ->where('no_rt_id', $noRtId);
                });
        });
    }

    $rows = $query->get(['tanggal', 'jenis_trans', 'sumber', 'nominal', 'metode', 'keterangan', 'tagihan_iuran_id']);
    $rtOptionsQuery = \App\Models\NoRt::query()->orderBy('nomor');
    if ($user && $user->role && $user->role->isRT() && optional($user->warga)->no_rt_id) {
        $rtOptionsQuery->where('no_rt_id', optional($user->warga)->no_rt_id);
    }
    $rtOptions = $rtOptionsQuery->pluck('nomor', 'no_rt_id')->toArray();
    $rt = $noRtId ? \App\Models\NoRt::find($noRtId) : null;
    $kelurahan = $request->query('kelurahan', 'Tanah Baru');
    $kecamatan = $request->query('kecamatan', 'Beji');
    $rw = $rt ? ($rt->rw ?: '010') : '010';
    $downloadDate = now()->format('Y-m-d');
    $initialBalance = (float) ($request->query('initial_balance', 0));
    $ketua = $noRtId ? \App\Models\KetuaRt::query()->active()->ketuaRt()->where('no_rt_id', $noRtId)->with('warga')->first() : null;
    $bendahara = $noRtId ? \App\Models\KetuaRt::query()->active()->bendaharaRt()->where('no_rt_id', $noRtId)->with('warga')->first() : null;
    $ketuaRtName = $ketua ? (optional($ketua->warga)->nama ?: ($ketua->nama ?? null)) : null;
    $bendaharaRtName = $bendahara ? (optional($bendahara->warga)->nama ?: ($bendahara->nama ?? null)) : null;

    $leftLogoSrc = null;
    if ($rt && $rt->nomor) {
        $nomor = str_pad((string) $rt->nomor, 3, '0', STR_PAD_LEFT);
        $base = 'rt-'.$nomor;
        $leftCandidates = [
            storage_path('app/public/logo/'.$base.'.png'),
            storage_path('app/public/logo/'.$base.'.jpg'),
            storage_path('app/public/logo/'.$base.'.jpeg'),
            storage_path('app/public/logo/'.strtoupper($base).'.png'),
            storage_path('app/public/logo/'.strtoupper($base).'.jpg'),
            storage_path('app/public/logo/'.strtoupper($base).'.jpeg'),
        ];
        foreach ($leftCandidates as $f) {
            if (is_file($f)) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                $leftLogoSrc = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($f));
                break;
            }
        }
    }
    $rightLogoSrc = null;
    $rightCandidates = [
        storage_path('app/public/logo/logokanan.png'),
        storage_path('app/public/logo/logokanan.jpg'),
        storage_path('app/public/logo/logokanan.jpeg'),
    ];
    foreach ($rightCandidates as $f) {
        if (is_file($f)) {
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
            $rightLogoSrc = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($f));
            break;
        }
    }

    $pdf = Pdf::setOptions([
        'chroot' => public_path(),
        'isRemoteEnabled' => false,
        'isHtml5ParserEnabled' => true,
        'allowed_protocols' => [
            'data://' => ['rules' => []],
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
        ],
    ])->loadView('exports.rekap-keuangan-pdf', [
        'rows' => $rows,
        'start' => $start,
        'end' => $end,
        'category' => $category,
        'no_rt_id' => $noRtId,
        'rtOptions' => $rtOptions,
        'download_date' => $downloadDate,
        'saldo_awal' => $initialBalance,
        'total_masuk' => (float) $rows->filter(fn ($r) => strtolower((string) $r->jenis) === 'masuk')->sum('nominal'),
        'total_keluar' => (float) $rows->filter(fn ($r) => strtolower((string) $r->jenis) === 'keluar')->sum('nominal'),
        'rw' => $rw,
        'kelurahan' => $kelurahan,
        'kecamatan' => $kecamatan,
        'leftLogoSrc' => $leftLogoSrc,
        'rightLogoSrc' => $rightLogoSrc,
        'ketuaRtName' => $ketuaRtName,
        'bendaharaRtName' => $bendaharaRtName,
    ])->setPaper('a4', 'portrait');

    $filename = sprintf('laporan-keuangan_%s_%s%s.pdf', $start ?: 'all', $end ?: 'all', $noRtId ? ('_'.$noRtId) : '');

    return $pdf->download($filename);
})->name('admin.rekap-keuangan.download-pdf');

Route::get('/admin/rekap-keuangan/preview-pdf', function (Request $request) {
    $start = $request->query('start');
    $end = $request->query('end');
    $category = $request->query('category');
    $noRtId = $request->query('no_rt_id');
    $user = auth()->user();
    if ($user && $user->role && $user->role->isRT()) {
        $noRtId = optional($user->warga)->no_rt_id;
    }

    $query = RekapKeuangan::query()->with(['tagihan.warga', 'tagihan.periode'])->orderBy('tanggal');
    try {
        if ($start && $end) {
            $s = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
            $e = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $end)->endOfDay();
            $query->whereBetween('tanggal', [$s, $e]);
        }
    } catch (\Throwable $e) {
    }
    if ($category) {
        $query->where(function ($q) use ($category) {
            $q->where('jenis_trans', 'keluar')
                ->orWhere(function ($qq) use ($category) {
                    $qq->where('jenis_trans', 'masuk')
                        ->where('sumber', $category);
                });
        });
    }
    if ($noRtId) {
        $query->where(function ($q) use ($noRtId) {
            $q->whereHas('tagihan.warga', function ($w) use ($noRtId) {
                $w->where('no_rt_id', $noRtId);
            })
                ->orWhere(function ($qq) use ($noRtId) {
                    $qq->whereNull('tagihan_iuran_id')
                        ->where('no_rt_id', $noRtId);
                });
        });
    }

    $rows = $query->get(['tanggal', 'jenis_trans', 'sumber', 'nominal', 'metode', 'keterangan', 'tagihan_iuran_id']);
    $rtOptionsQuery = \App\Models\NoRt::query()->orderBy('nomor');
    if ($user && $user->role && $user->role->isRT() && optional($user->warga)->no_rt_id) {
        $rtOptionsQuery->where('no_rt_id', optional($user->warga)->no_rt_id);
    }
    $rtOptions = $rtOptionsQuery->pluck('nomor', 'no_rt_id')->toArray();
    $rt = $noRtId ? \App\Models\NoRt::find($noRtId) : null;
    $kelurahan = $request->query('kelurahan', 'Tanah Baru');
    $kecamatan = $request->query('kecamatan', 'Beji');
    $rw = $rt ? ($rt->rw ?: '010') : '010';
    $downloadDate = now()->format('Y-m-d');
    $initialBalance = (float) ($request->query('initial_balance', 0));
    $ketua = $noRtId ? \App\Models\KetuaRt::query()->active()->ketuaRt()->where('no_rt_id', $noRtId)->with('warga')->first() : null;
    $bendahara = $noRtId ? \App\Models\KetuaRt::query()->active()->bendaharaRt()->where('no_rt_id', $noRtId)->with('warga')->first() : null;
    $ketuaRtName = $ketua ? (optional($ketua->warga)->nama ?: ($ketua->nama ?? null)) : null;
    $bendaharaRtName = $bendahara ? (optional($bendahara->warga)->nama ?: ($bendahara->nama ?? null)) : null;

    $leftLogoSrc = null;
    if ($rt && $rt->nomor) {
        $nomor = str_pad((string) $rt->nomor, 3, '0', STR_PAD_LEFT);
        $base = 'rt-'.$nomor;
        $leftCandidates = [
            storage_path('app/public/logo/'.$base.'.png'),
            storage_path('app/public/logo/'.$base.'.jpg'),
            storage_path('app/public/logo/'.$base.'.jpeg'),
            storage_path('app/public/logo/'.strtoupper($base).'.png'),
            storage_path('app/public/logo/'.strtoupper($base).'.jpg'),
            storage_path('app/public/logo/'.strtoupper($base).'.jpeg'),
        ];
        foreach ($leftCandidates as $f) {
            if (is_file($f)) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                $leftLogoSrc = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($f));
                break;
            }
        }
    }
    $rightLogoSrc = null;
    $rightCandidates = [
        storage_path('app/public/logo/logokanan.png'),
        storage_path('app/public/logo/logokanan.jpg'),
        storage_path('app/public/logo/logokanan.jpeg'),
    ];
    foreach ($rightCandidates as $f) {
        if (is_file($f)) {
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
            $rightLogoSrc = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($f));
            break;
        }
    }

    $pdf = Pdf::setOptions([
        'chroot' => public_path(),
        'isRemoteEnabled' => false,
        'isHtml5ParserEnabled' => true,
        'allowed_protocols' => [
            'data://' => ['rules' => []],
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
        ],
    ])->loadView('exports.rekap-keuangan-pdf', [
        'rows' => $rows,
        'start' => $start,
        'end' => $end,
        'category' => $category,
        'no_rt_id' => $noRtId,
        'rtOptions' => $rtOptions,
        'download_date' => $downloadDate,
        'saldo_awal' => $initialBalance,
        'total_masuk' => (float) $rows->filter(fn ($r) => strtolower((string) $r->jenis) === 'masuk')->sum('nominal'),
        'total_keluar' => (float) $rows->filter(fn ($r) => strtolower((string) $r->jenis) === 'keluar')->sum('nominal'),
        'rw' => $rw,
        'kelurahan' => $kelurahan,
        'kecamatan' => $kecamatan,
        'leftLogoSrc' => $leftLogoSrc,
        'rightLogoSrc' => $rightLogoSrc,
        'ketuaRtName' => $ketuaRtName,
        'bendaharaRtName' => $bendaharaRtName,
    ])->setPaper('a4', 'portrait');

    $filename = sprintf('laporan-keuangan_%s_%s%s.pdf', $start ?: 'all', $end ?: 'all', $noRtId ? ('_'.$noRtId) : '');

    return $pdf->stream($filename);
})->name('admin.rekap-keuangan.preview-pdf');

Route::get('/admin/rekap-keuangan/preview-html', function (Request $request) {
    $start = $request->query('start');
    $end = $request->query('end');
    $category = $request->query('category');
    $noRtId = $request->query('no_rt_id');
    $user = auth()->user();
    if ($user && $user->role && $user->role->isRT()) {
        $noRtId = optional($user->warga)->no_rt_id;
    }

    $query = RekapKeuangan::query()->with(['tagihan.warga', 'tagihan.periode'])->orderBy('tanggal');
    try {
        if ($start && $end) {
            $s = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
            $e = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $end)->endOfDay();
            $query->whereBetween('tanggal', [$s, $e]);
        }
    } catch (\Throwable $e) {
    }
    if ($category) {
        $query->where(function ($q) use ($category) {
            $q->where('jenis_trans', 'keluar')
                ->orWhere(function ($qq) use ($category) {
                    $qq->where('jenis_trans', 'masuk')
                        ->where('sumber', $category);
                });
        });
    }
    if ($noRtId) {
        $query->where(function ($q) use ($noRtId) {
            $q->whereHas('tagihan.warga', function ($w) use ($noRtId) {
                $w->where('no_rt_id', $noRtId);
            })
                ->orWhere(function ($qq) use ($noRtId) {
                    $qq->whereNull('tagihan_iuran_id')
                        ->where('no_rt_id', $noRtId);
                });
        });
    }

    $rows = $query->get(['tanggal', 'jenis_trans', 'sumber', 'nominal', 'metode', 'keterangan', 'tagihan_iuran_id']);
    $rtOptionsQuery = \App\Models\NoRt::query()->orderBy('nomor');
    if ($user && $user->role && $user->role->isRT() && optional($user->warga)->no_rt_id) {
        $rtOptionsQuery->where('no_rt_id', optional($user->warga)->no_rt_id);
    }
    $rtOptions = $rtOptionsQuery->pluck('nomor', 'no_rt_id')->toArray();
    $rt = $noRtId ? \App\Models\NoRt::find($noRtId) : null;
    $kelurahan = $request->query('kelurahan', 'Tanah Baru');
    $kecamatan = $request->query('kecamatan', 'Beji');
    $rw = $rt ? ($rt->rw ?: '010') : '010';
    $downloadDate = now()->format('Y-m-d');
    $initialBalance = (float) ($request->query('initial_balance', 0));
    $ketua = $noRtId ? \App\Models\KetuaRt::query()->active()->ketuaRt()->where('no_rt_id', $noRtId)->with('warga')->first() : null;
    $bendahara = $noRtId ? \App\Models\KetuaRt::query()->active()->bendaharaRt()->where('no_rt_id', $noRtId)->with('warga')->first() : null;
    $ketuaRtName = $ketua ? (optional($ketua->warga)->nama ?: ($ketua->nama ?? null)) : null;
    $bendaharaRtName = $bendahara ? (optional($bendahara->warga)->nama ?: ($bendahara->nama ?? null)) : null;

    $leftLogoSrc = null;
    if ($rt && $rt->nomor) {
        $nomor = str_pad((string) $rt->nomor, 3, '0', STR_PAD_LEFT);
        $base = 'rt-'.$nomor;
        $leftCandidates = [
            storage_path('app/public/logo/'.$base.'.png'),
            storage_path('app/public/logo/'.$base.'.jpg'),
            storage_path('app/public/logo/'.$base.'.jpeg'),
            storage_path('app/public/logo/'.strtoupper($base).'.png'),
            storage_path('app/public/logo/'.strtoupper($base).'.jpg'),
            storage_path('app/public/logo/'.strtoupper($base).'.jpeg'),
        ];
        foreach ($leftCandidates as $f) {
            if (is_file($f)) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                $leftLogoSrc = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($f));
                break;
            }
        }
    }
    $rightLogoSrc = null;
    $rightCandidates = [
        storage_path('app/public/logo/logokanan.png'),
        storage_path('app/public/logo/logokanan.jpg'),
        storage_path('app/public/logo/logokanan.jpeg'),
    ];
    foreach ($rightCandidates as $f) {
        if (is_file($f)) {
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
            $rightLogoSrc = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($f));
            break;
        }
    }

    return view('exports.rekap-keuangan-pdf', [
        'rows' => $rows,
        'start' => $start,
        'end' => $end,
        'category' => $category,
        'no_rt_id' => $noRtId,
        'rtOptions' => $rtOptions,
        'download_date' => $downloadDate,
        'saldo_awal' => $initialBalance,
        'total_masuk' => (float) $rows->filter(fn ($r) => strtolower((string) $r->jenis) === 'masuk')->sum('nominal'),
        'total_keluar' => (float) $rows->filter(fn ($r) => strtolower((string) $r->jenis) === 'keluar')->sum('nominal'),
        'rw' => $rw,
        'kelurahan' => $kelurahan,
        'kecamatan' => $kecamatan,
        'leftLogoSrc' => $leftLogoSrc,
        'rightLogoSrc' => $rightLogoSrc,
        'ketuaRtName' => $ketuaRtName,
        'bendaharaRtName' => $bendaharaRtName,
    ]);
})->name('admin.rekap-keuangan.preview-html');

// Warga verification routes
Route::post('/admin/resources/wargas/verify/{warga}', [WargaVerificationController::class, 'verify'])
    ->name('filament.admin.resources.wargas.verify');
Route::post('/admin/resources/wargas/reject/{warga}', [WargaVerificationController::class, 'reject'])
    ->name('filament.admin.resources.wargas.reject');

Route::get('/reset-password/warga', [WargaPasswordResetController::class, 'showEmailForm'])
    ->name('warga.password.request');
Route::post('/reset-password/warga', [WargaPasswordResetController::class, 'sendOtp'])
    ->name('warga.password.send-otp');

Route::get('/verifikasi/otp-kode/warga', [WargaPasswordResetController::class, 'showOtpForm'])
    ->name('warga.password.verify-otp');
Route::post('/verifikasi/otp-kode/warga', [WargaPasswordResetController::class, 'verifyOtp'])
    ->name('warga.password.check-otp');

Route::get('/reset-password/baru', [WargaPasswordResetController::class, 'showNewPasswordForm'])
    ->name('warga.password.new');
Route::post('/reset-password/baru', [WargaPasswordResetController::class, 'updatePassword'])
    ->name('warga.password.update');
