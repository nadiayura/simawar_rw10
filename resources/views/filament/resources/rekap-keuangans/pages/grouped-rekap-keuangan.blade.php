<x-filament::page>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .rk-group{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:14px;overflow:hidden}
        .rk-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e5e7eb;font-weight:600;color:#111827}
        .rk-actions{display:flex;align-items:center;gap:8px}
        .rk-body{padding:0 12px 12px 12px}
        .badge{display:inline-block;padding:2px 8px;border-radius:9999px;font-size:12px}
        .badge-success{background:#e0f4fb;color:#2ea7d6}
        .badge-danger{background:#fee2e2;color:#991b1b}
        .badge-gray{background:#f3f4f6;color:#374151}
        .table{width:100%;border-collapse:collapse;table-layout:fixed}
        .th,.td{padding:10px 8px;font-size:13px;color:#111827}
        .th{color:#374151}
        .row{border-top:1px solid #eef2f7}
        .btn{background:#2ea7d6;color:#fff;padding:6px 10px;border-radius:8px;font-size:13px;text-decoration:none}
        .btn:hover{background:#238cb5}
        .stat-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px}
        .sort-select{appearance:none;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;font-size:13px;color:#374151}
        .filters{gap:8px}
        .filter-btn{display:inline-block;padding:6px 12px;border:1px solid #e5e7eb;border-radius:9999px;font-size:13px;background:#fff;color:#374151;text-decoration:none}
        .filter-btn.active{background:#2ea7d6;color:#fff;border-color:#2ea7d6}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:9999}
        .modal-card{background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,.16);width:480px;max-width:94vw}
        .modal-header{padding:18px 20px 10px 20px;text-align:center}
        .modal-body{padding:0 24px 20px 24px;text-align:center;color:#4b5563;font-size:14px}
        .modal-footer{padding:16px 20px 20px 20px;display:flex;justify-content:center;gap:10px}
        .modal-title{font-size:18px;font-weight:600;color:#111827;margin-top:8px}
        .modal-icon{width:40px;height:40px;border-radius:9999px;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto}
    </style>

    <div
        x-data="{showRange:false, lock: @json($needs_sync ?? false)}"
        x-init="if(lock){ document.body.style.overflow='hidden' }"
        x-effect="document.body.style.overflow = lock ? 'hidden' : ''"
    >
        @if($needs_sync ?? false)
            <div class="modal-overlay">
                <div class="modal-card">
                    <div class="modal-header">
                        <div class="modal-icon">
                            <i class="fa-solid fa-circle-exclamation" style="font-size:20px;color:#b91c1c;"></i>
                        </div>
                        <div class="modal-title">Sinkron Pemasukan dari Iuran</div>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom:6px">
                            Terdapat data pembayaran iuran (Midtrans maupun Tunai) yang belum tersinkron
                            ke Rekap Keuangan.
                        </p>
                        @if(($unsynced_count ?? 0) > 0)
                            <p style="margin-bottom:6px;font-weight:500;color:#111827">
                                Terdeteksi {{ $unsynced_count }} transaksi baru yang perlu disinkron.
                            </p>
                        @endif
                        <p>
                            Silakan klik tombol di bawah untuk melakukan sinkronisasi. Halaman Rekap Keuangan
                            tidak dapat digunakan sebelum sinkronisasi selesai.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <a
                            href="{{ request()->fullUrlWithQuery(['do_sync_iuran' => 1]) }}"
                            class="btn"
                            style="background:#dc2626;border-radius:9999px;padding:8px 18px;font-weight:500"
                        >
                            Sinkron Pemasukan dari Iuran
                        </a>
                    </div>
                </div>
            </div>
        @endif

    <div class="filters" style="display:flex;gap:12px;margin-bottom:14px;flex-wrap:wrap;align-items:center">
        <a class="filter-btn{{ request()->query('range')==='month' ? ' active' : '' }}" href="?range=month">Bulan Ini</a>
        <a class="filter-btn{{ request()->query('range')==='week' ? ' active' : '' }}" href="?range=week">Minggu Ini</a>
        <a class="filter-btn{{ request()->query('range')==='year' ? ' active' : '' }}" href="?range=year">Tahun Ini</a>
        <button type="button" class="filter-btn{{ (request()->query('start') && request()->query('end')) ? ' active' : '' }}" @click="showRange = !showRange">Filter Tanggal</button>
        <div style="display:flex; align-items:flex-start; gap:12px; width:100%">
        <div style="display:flex; flex-direction:column; gap:8px">
            <form method="get" x-show="showRange" x-transition style="display:flex; align-items:center; gap:8px">
                <input type="date" name="start" value="{{ $start ?? '' }}" class="sort-select" />
                <span>s/d</span>
                <input type="date" name="end" value="{{ $end ?? '' }}" class="sort-select" />
                <button class="btn" type="submit">Terapkan</button>
            </form>
        </div>

        <!-- KANAN: Filter tahun -->
        <form method="get" style="margin-left:auto">
            <select name="year" class="sort-select" onchange="this.form.submit()">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </form>

    </div>

    </div>

    <div style="display:flex;flex-direction:column;gap:14px;height:max-content;">
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;align-items:stretch">
            <div style="height:100%;display:flex">
                <div class="rk-group" x-data="{open:true}" style="height:100%;display:flex;flex-direction:column;width:100%">
                    <div class="rk-header">
                        <div>Pemasukan</div>
                        <div class="rk-actions">
                            @php $userGlobal = auth()->user(); @endphp
                            @if($userGlobal && $userGlobal->role && ! $userGlobal->role->isWarga())
                                <form method="get">
                                    <select name="no_rt_id" class="sort-select" onchange="this.form.submit()">
                                        @foreach(($rtOptions ?? []) as $rid => $nomor)
                                            <option value="{{ $rid }}" {{ ($selectedRtId ?? null) === $rid ? 'selected' : '' }}>RT {{ $nomor }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="badge badge-gray">RT {{ ($rtOptions[$selectedRtId] ?? '') }}</span>
                            @endif
                            <div class="rt-toggle" @click="open=!open" style="cursor:pointer;display:flex;align-items:center;gap:8px;color:#374151">
                                <svg class="chev" :class="{ 'rot': open }" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                    </div>
                    <div class="rk-body" x-show="open" style="height:320px;overflow:auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="th">Tanggal</th>
                                    <th class="th">Metode</th>
                                    <th class="th">Keterangan</th>
                                    <th class="th" style="text-align:right">Nominal</th>
                                    <th class="th">Sumber</th>
                                    <th class="th"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($masuk_rows ?? []) as $r)
                                    <tr class="row" x-data="{ deleteOpen:false }">
                                        <td class="td">{{ $r['tanggal'] }}</td>
                                        <td class="td">{{ $r['metode'] }}</td>
                                        <td class="td">{{ $r['payer'] ?? ($r['keterangan'] ?? '-') }}</td>
                                        <td class="td" style="text-align:right">Rp {{ number_format((float) $r['nominal'], 0, ',', '.') }}</td>
                                        <td class="td">{{ $r['sumber'] }}</td>
                                        <td class="td">
                                            <button
                                                type="button"
                                                @click.prevent="deleteOpen = true"
                                                style="background:none;border:none;color:#dc2626;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:4px"
                                                aria-label="Hapus transaksi"
                                                title="Hapus"
                                            >
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                            <div
                                                x-show="deleteOpen"
                                                x-transition.opacity
                                                class="modal-overlay"
                                                @click.self="deleteOpen = false"
                                            >
                                                <div class="modal-card">
                                                    <div class="modal-header">
                                                        <div class="modal-icon">
                                                            <i class="fa-solid fa-trash-can" style="color:#dc2626;font-size:20px;"></i>
                                                        </div>
                                                        <div class="modal-title">Hapus Transaksi</div>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Apakah Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button
                                                            type="button"
                                                            @click="deleteOpen = false"
                                                            style="background:#f3f4f6;color:#374151;padding:8px 16px;border-radius:9999px;font-size:13px;min-width:96px"
                                                        >
                                                            Batal
                                                        </button>
                                                        <button
                                                            type="button"
                                                            @click="$wire.deleteTransaksi('{{ $r['id'] }}'); deleteOpen = false"
                                                            style="background:#dc2626;color:#fff;padding:8px 18px;border-radius:9999px;font-size:13px;font-weight:500;min-width:96px"
                                                        >
                                                            Hapus
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="row">
                                        <td class="td" colspan="6" style="color:#6b7280">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:12px">
               @php $user = auth()->user(); @endphp
               @if($user && $user->role && ! $user->role->isWarga())
                    <a class="btn" style="display:flex;justify-content:center;margin-bottom:5px;" href="{{ \App\Filament\Resources\RekapKeuangans\RekapKeuanganResource::getUrl('create') }}?jenis=masuk">Catat transaksi</a>
               @endif
               <!-- chart pendapat -->
                <div class="stat-card" style="height:fit-content;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="font-weight:700;color:#111827">Pendapatan</div>
                        </div>
                        <form method="get">
                            <select name="year" class="sort-select" onchange="this.form.submit()">
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>
                    @php
                        $sumMasuk = array_sum(($masuk_by_sumber ?? []));
                        $palette = ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#6366f1','#22c55e','#e11d48','#14b8a6','#f97316'];
                        $start = 0; $idx = 0; $segments = [];
                        foreach(($masuk_by_sumber ?? []) as $s => $v){
                            $p = $sumMasuk > 0 ? ($v / $sumMasuk) * 100 : 0;
                            $degStart = ($start * 3.6);
                            $degEnd = (($start + $p) * 3.6);
                            $color = $palette[$idx % count($palette)];
                            $segments[] = $color.' '.$degStart.'deg '.$degEnd.'deg';
                            $start += $p; $idx++;
                        }
                        $gradientMasuk = count($segments) ? 'conic-gradient('.implode(',', $segments).')' : 'conic-gradient(#e5e7eb 0deg 360deg)';
                    @endphp
                    <div style="display:flex;gap:12px;align-items:center">
                        <div style="width:160px;height:160px;border-radius:50%;background:{{ $gradientMasuk ?? 'conic-gradient(#e5e7eb 0deg 360deg)' }};position:relative">
                            <div style="position:absolute;inset:24px;border-radius:50%;background:#fff"></div>
                        </div>
                        <div style="flex:1">
                            @php
                                $i=0;
                                $sumMasukLegend = (float) array_sum(($masuk_by_sumber ?? []));
                                $legendPalette = ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#6366f1','#22c55e','#e11d48','#14b8a6','#f97316'];
                            @endphp
                            @foreach(($masuk_by_sumber ?? []) as $s => $v)
                                @php
                                    $p = $sumMasukLegend > 0 ? round(($v / $sumMasukLegend) * 100) : 0;
                                    $color = $legendPalette[$i % count($legendPalette)];
                                    $i++;
                                @endphp
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                                    <span style="width:10px;height:10px;border-radius:2px;background:{{ $color }}"></span>
                                    <span style="flex:1;color:#374151;font-size:12px">{{ ucfirst($s) }}</span>
                                    <span style="width:40px;text-align:right;color:#374151;font-size:12px">{{ $p }}%</span>
                                </div>
                            @endforeach
                            @if(empty($masuk_by_sumber))
                                <div style="color:#6b7280;font-size:12px">Tidak ada data.</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                <div style="color:#6b7280;font-size:12px;">Total Pemasukan</div>
                    <div style="font-weight:700;font-size:18px;color:#111827">
                        Rp {{ number_format((float) ($masuk_total ?? 0), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>


        <div style="display:grid;grid-template-columns:1fr 2fr;gap:14px;align-items:stretch">
            <div style="display:flex;flex-direction:column;gap:12px;height:100%">
                @php $user = auth()->user(); @endphp
                @if($user && $user->role && ! $user->role->isWarga())
                    <a class="btn" style="display:flex;justify-content:center;margin-bottom:5px;" href="{{ \App\Filament\Resources\RekapKeuangans\RekapKeuanganResource::getUrl('create') }}?jenis=keluar">Catat Transaksi</a>

                @endif
                <div class="stat-card" style="height:100%;display:flex;flex-direction:column">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="font-weight:700;color:#111827">Pengeluaran</div>
                        </div>
                        <form method="get">
                            <select name="year" class="sort-select" onchange="this.form.submit()">
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>
                    @php
                        $sumKeluar = array_sum(($keluar_by_sumber ?? []));
                        $palette2 = ['#6366f1','#8b5cf6','#3b82f6','#0ea5e9','#06b6d4','#22d3ee','#14b8a6','#84cc16','#f59e0b','#ef4444'];
                        $start2 = 0; $idx2 = 0; $segments2 = [];
                        foreach(($keluar_by_sumber ?? []) as $s => $v){
                            $p = $sumKeluar > 0 ? ($v / $sumKeluar) * 100 : 0;
                            $degStart = ($start2 * 3.6);
                            $degEnd = (($start2 + $p) * 3.6);
                            $color = $palette2[$idx2 % count($palette2)];
                            $segments2[] = $color.' '.$degStart.'deg '.$degEnd.'deg';
                            $start2 += $p; $idx2++;
                        }
                        $gradientKeluar = count($segments2) ? 'conic-gradient('.implode(',', $segments2).')' : 'conic-gradient(#e5e7eb 0deg 360deg)';
                    @endphp
                    <div style="display:flex;gap:12px;align-items:center;flex:1">
                        <div style="width:160px;height:160px;border-radius:50%;background:{{ $gradientKeluar ?? 'conic-gradient(#e5e7eb 0deg 360deg)' }};position:relative">
                            <div style="position:absolute;inset:24px;border-radius:50%;background:#fff"></div>
                        </div>
                        <div style="flex:1">
                            @php
                                $i2=0;
                                $sumKeluarLegend = (float) array_sum(($keluar_by_sumber ?? []));
                                $legendPalette2 = ['#6366f1','#8b5cf6','#3b82f6','#0ea5e9','#06b6d4','#22d3ee','#14b8a6','#84cc16','#f59e0b','#ef4444'];
                            @endphp
                            @foreach(($keluar_by_sumber ?? []) as $s => $v)
                                @php
                                    $p = $sumKeluarLegend > 0 ? round(($v / $sumKeluarLegend) * 100) : 0;
                                    $color = $legendPalette2[$i2 % count($legendPalette2)];
                                    $i2++;
                                @endphp
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                                    <span style="width:10px;height:10px;border-radius:2px;background:{{ $color }}"></span>
                                    <span style="flex:1;color:#374151;font-size:12px">{{ ucfirst($s) }}</span>
                                    <span style="width:40px;text-align:right;color:#374151;font-size:12px">{{ $p }}%</span>
                                </div>
                            @endforeach
                            @if(empty($keluar_by_sumber))
                                <div style="color:#6b7280;font-size:12px">Tidak ada data.</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="stat-card">
            <div style="font-weight:700;color:#111827;margin-bottom:10px">Total Pengeluaran</div>
            <div style="font-weight:700">{{ 'Rp '.number_format((float) ($keluar_total ?? 0), 0, ',', '.') }}</div>
        </div>
            </div>
            <div>
                <div class="rk-group" x-data="{open:true}" style="height:100%;display:flex;flex-direction:column">
                    <div class="rk-header">
                        <div>Pengeluaran</div>
                        <div class="rk-actions">
                            @php $userGlobal2 = auth()->user(); @endphp
                            @if($userGlobal2 && $userGlobal2->role && ! $userGlobal2->role->isWarga())
                                <form method="get">
                                    <select name="no_rt_id" class="sort-select" onchange="this.form.submit()">
                                        @foreach(($rtOptions ?? []) as $rid => $nomor)
                                            <option value="{{ $rid }}" {{ ($selectedRtId ?? null) === $rid ? 'selected' : '' }}>RT {{ $nomor }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="badge badge-gray">RT {{ ($rtOptions[$selectedRtId] ?? '') }}</span>
                            @endif
                            <div class="rt-toggle" @click="open=!open" style="cursor:pointer;display:flex;align-items:center;gap:8px;color:#374151">
                                <svg class="chev" :class="{ 'rot': open }" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                    </div>
                    <div class="rk-body" x-show="open" style="height:320px;overflow:auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="th">Tanggal</th>
                                    <th class="th">Metode</th>
                                    <th class="th">Keterangan</th>
                                    <th class="th" style="text-align:right">Nominal</th>
                                    <th class="th">Sumber</th>
                                    <th class="th"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($keluar_rows ?? []) as $r)
                                    <tr class="row" x-data="{ deleteOpen:false }">
                                        <td class="td">{{ (request()->query('range') === 'month' && !empty($r['tanggal'])) ? substr($r['tanggal'], 0, 7) : ($r['tanggal'] ?? '-') }}</td>
                                        <td class="td">{{ $r['metode'] }}</td>
                                        <td class="td">{{ $r['keterangan'] ?? '-' }}</td>
                                        <td class="td" style="text-align:right">Rp {{ number_format((float) $r['nominal'], 0, ',', '.') }}</td>
                                        <td class="td">{{ $r['sumber'] }}</td>
                                        <td class="td">
                                            <button
                                                type="button"
                                                @click.prevent="deleteOpen = true"
                                                style="background:none;border:none;color:#dc2626;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:4px"
                                                aria-label="Hapus transaksi"
                                                title="Hapus"
                                            >
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                            <div
                                                x-show="deleteOpen"
                                                x-transition.opacity
                                                class="modal-overlay"
                                                @click.self="deleteOpen = false"
                                            >
                                                <div class="modal-card">
                                                    <div class="modal-header">
                                                        <div class="modal-icon">
                                                            <i class="fa-solid fa-trash-can" style="color:#dc2626;font-size:20px;"></i>
                                                        </div>
                                                        <div class="modal-title">Hapus Transaksi</div>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Apakah Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button
                                                            type="button"
                                                            @click="deleteOpen = false"
                                                            style="background:#f3f4f6;color:#374151;padding:8px 16px;border-radius:9999px;font-size:13px;min-width:96px"
                                                        >
                                                            Batal
                                                        </button>
                                                        <button
                                                            type="button"
                                                            @click="$wire.deleteTransaksi('{{ $r['id'] }}'); deleteOpen = false"
                                                            style="background:#dc2626;color:#fff;padding:8px 18px;border-radius:9999px;font-size:13px;font-weight:500;min-width:96px"
                                                        >
                                                            Hapus
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="row">
                                        <td class="td" colspan="6" style="color:#6b7280">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-filament::page>
