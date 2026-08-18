<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Keuangan RT</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .header {
            margin-bottom: 6px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
        }

        .header img {
            height: 70px;
        }

        .header-center {
            text-align: center;
        }

        .header-center .title {
            font-weight: 700;
            font-size: 16px;
        }

        .header-center .subtitle {
            font-size: 12px;
            font-weight: 600;
        }

        .line {
            border-top: 2px solid #000;
            margin: 6px 0 10px;
        }

        .periode {
            text-align: center;
            font-weight: 700;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px 6px;
        }

        th {
            text-align: center;
            font-weight: 700;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .section {
            font-weight: 700;
            background: #fff;
        }

        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature .col {
            width: 40%;
            text-align: center;
        }

        .signature .place {
            text-align: right;
            margin-bottom: 40px;
        }
    </style>
</head>

<body>

    @php
    $leftLogo = '';
    $leftMime = 'image/png';
    try {
    $rtNumber = $rtOptions[$no_rt_id] ?? null;
    if ($rtNumber !== null) {
    $rtNumber = str_pad((string) $rtNumber, 3, '0', STR_PAD_LEFT);
    $baseName = 'rt-' . $rtNumber;
    $candidates = [
    storage_path('app/public/logo/' . $baseName . '.png'),
    storage_path('app/public/logo/' . $baseName . '.jpg'),
    storage_path('app/public/logo/' . $baseName . '.jpeg'),
    storage_path('app/public/logo/' . strtoupper($baseName) . '.png'),
    storage_path('app/public/logo/' . strtoupper($baseName) . '.jpg'),
    storage_path('app/public/logo/' . strtoupper($baseName) . '.jpeg'),
    ];
    foreach ($candidates as $path) {
    if (is_file($path)) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $leftMime = $ext === 'png' ? 'image/png' : 'image/jpeg';
    $leftLogo = base64_encode(file_get_contents($path));
    break;
    }
    }
    }
    } catch (\Throwable $e) {
    }
    @endphp
    @php
    $rightLogo = '';
    $rightMime = 'image/png';
    try {
    $rightCandidates = [
    storage_path('app/public/logo/logokanan.png'),
    storage_path('app/public/logo/logokanan.jpg'),
    storage_path('app/public/logo/logokanan.jpeg'),
    storage_path('app/public/logo/LOGOKANAN.png'),
    storage_path('app/public/logo/LOGOKANAN.jpg'),
    storage_path('app/public/logo/LOGOKANAN.jpeg'),
    ];
    foreach ($rightCandidates as $rightPath) {
    if (is_file($rightPath)) {
    $ext = strtolower(pathinfo($rightPath, PATHINFO_EXTENSION));
    $rightMime = $ext === 'png' ? 'image/png' : 'image/jpeg';
    $rightLogo = base64_encode(file_get_contents($rightPath));
    break;
    }
    }
    } catch (\Throwable $e) {
    }
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:20%; text-align:left">
                    @if ($leftLogo)
                    <img src="data:{{ $leftMime }};base64,{{ $leftLogo }}" alt="Logo RT">
                    @endif
                </td>
                <td style="width:60%;">
                    <div class="header-center">
                        <div class="title">RUKUN WARGA {{ $rw ?? '010' }}</div>
                        <div class="subtitle">REKAPITULASI KEUANGAN RT ({{ $rtOptions[$no_rt_id] ?? '' }})</div>
                        <div class="subtitle">
                            KE {{ strtoupper($kelurahan ?? 'TANAH BARU') }}, KECAMATAN {{ strtoupper($kecamatan ?? 'BEJI') }}, DEPOK
                        </div>
                    </div>
                </td>
                <td style="width:20%; text-align:right">
                    @if ($rightLogo)
                    <img src="data:{{ $rightMime }};base64,{{ $rightLogo }}" alt="Logo Kota">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="line"></div>

    @php
        $periodeText = null;
        try {
            $sObj = $start ? \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $start) : null;
            $eObj = $end ? \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $end) : null;
        } catch (\Throwable $e) {
            $sObj = null;
            $eObj = null;
        }
        if ($sObj && $eObj && $sObj->isSameMonth($eObj)) {
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            $periodeText = 'BULAN : ' . ($months[(int) $sObj->month] ?? $sObj->format('F')) . ' ' . $sObj->year;
        } else {
            $periodeText = 'PERIODE : ' . ($start ?? '-') . ' s/d ' . ($end ?? '-');
        }
    @endphp
    <div class="periode">{{ $periodeText }}</div>

    {{-- TABEL --}}
    @php($masukRows = ($rows ?? collect())->filter(fn($r) => strtolower((string) $r->jenis) === 'masuk'))
    @php($keluarRows = ($rows ?? collect())->filter(fn($r) => strtolower((string) $r->jenis) === 'keluar'))
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:5%">NO.</th>
                <th rowspan="2" style="width:15%">TANGGAL</th>
                <th rowspan="2">URAIAN / RINCIAN</th>
                <th colspan="2">MUTASI</th>
                <th rowspan="2" style="width:15%">SALDO KAS<br>RP</th>
            </tr>
            <tr>
                <th style="width:15%">KAS MASUK<br>RP</th>
                <th style="width:15%">KAS KELUAR<br>RP</th>
            </tr>
        </thead>
        <tbody>
            {{-- A --}}
            <tr>
                <td colspan="3" class="section">A. Total Saldo</td>
                <td></td>
                <td></td>
                <td class="right">Rp {{ number_format((float) ($saldo_awal + ($total_masuk ?? 0) - ($total_keluar ?? 0)), 0, ',', '.') }}</td>
            </tr>

            {{-- B --}}
            <tr>
                <td colspan="5" class="section">B. PEMASUKAN IURAN WARGA RT. {{ $rtOptions[$no_rt_id] ?? '' }}</td>
                <td></td>
            </tr>
            @php($saldo = (float) ($saldo_awal ?? 0))
            @php($no = 1)
            @foreach($masukRows as $r)
            @php($saldo += (float) $r->nominal)
            <tr>
                <td class="center">{{ $no++ }}</td>
                <td class="center">{{ optional($r->tanggal)->format('Y-m-d') }}</td>
                @php($payer = optional(optional($r->tagihan)->warga)->nama ?: ($r->keterangan ?? null))
                @php($uraianMasuk = strtolower((string) $r->sumber) === 'donasi'
                ? ('Donasi dari '.($payer ?: '-'))
                : ('Iuran warga '.($payer ?: '-')))
                @php($bulanNama = optional(optional($r->tagihan)->periode)->nama_bulan)
                @php($tahunPeriode = optional(optional($r->tagihan)->periode)->tahun)
                @php($bulanLabel = $bulanNama && $tahunPeriode ? ($bulanNama.' '.$tahunPeriode) : (optional($r->tanggal)->format('Y-m') ?: null))
                <td>{{ $uraianMasuk }} secara {{ $r->metode }}{{ $bulanLabel ? (' untuk bulan '.$bulanLabel) : '' }}</td>
                <td class="right">Rp {{ number_format((float) $r->nominal, 0, ',', '.') }}</td>
                <td class="right">-</td>
                <td class="right">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td class="right" style="font-weight:700">JUMLAH</td>
                <td class="right" style="font-weight:700">Rp {{ number_format((float) ($total_masuk ?? 0), 0, ',', '.') }}</td>
                <td class="right">-</td>
                <td class="right" style="font-weight:700">Rp {{ number_format((float) ($saldo_awal + ($total_masuk ?? 0)), 0, ',', '.') }}</td>
            </tr>

            {{-- C --}}

            <tr>
                <td colspan="5" class="section"> C.PENGELUARAN / BIAYA OPERASIONAL RT. {{ $rtOptions[$no_rt_id] ?? '' }}</td>
                <td></td>
            </tr>

            @php($no = 1)
            @foreach($keluarRows as $r)
            @php($saldo -= (float) $r->nominal)
            <tr>
                <td class="center">{{ $no++ }}</td>
                <td class="center">{{ optional($r->tanggal)->format('Y-m-d') }}</td>
                <td>{{ $r->keterangan ?? '-' }} secara {{ $r->metode }}</td>
                <td class="right">-</td>
                <td class="right">Rp {{ number_format((float) $r->nominal, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td class="right" style="font-weight:700">JUMLAH</td>
                <td class="right" style="font-weight:700">Rp {{ number_format((float) ($total_masuk ?? 0), 0, ',', '.') }}</td>
                <td class="right" style="font-weight:700">Rp {{ number_format((float) ($total_keluar ?? 0), 0, ',', '.') }}</td>
                <td class="right" style="font-weight:700">Rp {{ number_format((float) ($saldo_awal + ($total_masuk ?? 0) - ($total_keluar ?? 0)), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table style="width:100%; border-collapse:collapse; margin-top:20px">
        <tr>
            <td style="text-align:right; border:none" colspan="2">Depok, {{ $download_date ?? now()->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td style="width:50%; text-align:center; border:none">Bendahara RT {{ $rtOptions[$no_rt_id] ?? '' }}</td>
            <td style="width:50%; text-align:center; border:none">Ketua RT {{ $rtOptions[$no_rt_id] ?? '' }}</td>
        </tr>
        <tr>
            <td style="height:60px; border:none"></td>
            <td style="height:60px; border:none"></td>
        </tr>
        <tr>
            <td style="text-align:center; border:none">({{ $bendaharaRtName ?? '................................' }})</td>
            <td style="text-align:center; border:none">({{ $ketuaRtName ?? '................................' }})</td>
        </tr>
    </table>

</body>

</html>
