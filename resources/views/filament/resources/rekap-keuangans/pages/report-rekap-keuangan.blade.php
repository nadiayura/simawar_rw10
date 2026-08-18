<x-filament::page>
    <style>
        .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px;margin-bottom:14px}
        .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
        .label{color:#6b7280;font-size:12px;margin-bottom:6px}
        .input{width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:14px;color:#111827;background:#fff}
        .select{appearance:none;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:14px;color:#374151}
        .btn{background:#2563eb;color:#fff;padding:8px 12px;border-radius:8px;font-size:13px;text-decoration:none;display:inline-block}
        .btn:hover{background:#1d4ed8}
        .table{width:100%;border-collapse:collapse}
        .th,.td{padding:10px 8px;font-size:13px;color:#111827}
        .th{color:#374151}
        .row{border-top:1px solid #eef2f7}
    </style>

    @php
        $req = request();
        $bulan = (int) $req->get('bulan');
        $tahun = (int) $req->get('tahun');
        if ($bulan && $tahun) {
            $start = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
            $end = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
            $show = true;
        }
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $currentYear = (int) now()->year;
        $years = range($currentYear, $currentYear - 4);
    @endphp

    <div class="card">
        <div style="font-weight:700;color:#111827;margin-bottom:10px">Filter Rekap Keuangan</div>
        <form method="get" style="display:flex;flex-direction:column;gap:12px">
            <div class="grid">
                <div>
                    <div class="label">Dari Tanggal</div>
                    <input class="input" type="date" name="start" value="{{ $start ?? '' }}">
                </div>
                <div>
                    <div class="label">Sampai Tanggal</div>
                    <input class="input" type="date" name="end" value="{{ $end ?? '' }}">
                </div>
               
            </div>
            <div class="grid">
                <div>
                    <div class="label">Bulan</div>
                    <select class="select" name="bulan">
                        <option value="">Pilih Bulan</option>
                        @foreach($months as $mValue => $mLabel)
                            <option value="{{ $mValue }}" {{ (int)($bulan ?? 0) === (int)$mValue ? 'selected' : '' }}>{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="label">Tahun</div>
                    <select class="select" name="tahun">
                        <option value="">Pilih Tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ (int)($tahun ?? 0) === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="label">No RT</div>
                    <select class="select" name="no_rt_id">
                        @foreach(($rtOptions ?? []) as $id => $label)
                            <option value="{{ $id }}" {{ ($no_rt_id ?? '') === $id ? 'selected' : '' }}>RT {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <input type="hidden" name="show" value="1">
            <div>
                <button type="submit" class="btn">Tampilkan</button>
            </div>
        </form>
    </div>

    @if(($show ?? false) === true)
    <div class="card">
        <div style="font-weight:700;color:#111827;margin-bottom:10px">Data Rekap Keuangan</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px">
            <div>
                <div class="label">Dari Tanggal</div>
                <div>{{ $start ?? '-' }}</div>
            </div>
            <div>
                <div class="label">Sampai Tanggal</div>
                <div>{{ $end ?? '-' }}</div>
            </div>
            <div>
                <div class="label">Kategori</div>
                <div>{{ $category ? ucfirst($category) : 'Semua Kategori' }}</div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px">
            <div>
                <div class="label">No RT</div>
                <div>{{ ($no_rt_id ?? '') ? ('RT '.($rtOptions[$no_rt_id] ?? $no_rt_id)) : 'Pilih RT' }}</div>
            </div>
            <div>
                <div class="label">Tanggal Unduh</div>
                <div>{{ $download_date ?? '-' }}</div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px">
            <div>
                <div class="label">Total Masuk</div>
                <div>Rp {{ number_format((float) ($total_masuk ?? 0), 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="label">Total Keluar</div>
                <div>Rp {{ number_format((float) ($total_keluar ?? 0), 0, ',', '.') }}</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;margin-bottom:12px">
            <a class="btn" href="{{ route('admin.rekap-keuangan.preview-html', ['start' => $start, 'end' => $end, 'category' => $category, 'no_rt_id' => $no_rt_id, 'initial_balance' => $initial_balance]) }}" target="_blank">Preview HTML</a>
            <a class="btn" href="{{ route('admin.rekap-keuangan.preview-pdf', ['start' => $start, 'end' => $end, 'category' => $category, 'no_rt_id' => $no_rt_id, 'initial_balance' => $initial_balance]) }}" target="_blank">Preview PDF</a>
            <a class="btn" href="{{ route('admin.rekap-keuangan.download-pdf', ['start' => $start, 'end' => $end, 'category' => $category, 'no_rt_id' => $no_rt_id, 'initial_balance' => $initial_balance]) }}">Cetak PDF</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th class="th">Tanggal</th>
                    <th class="th">Jenis</th>
                    <th class="th">Metode</th>
                    <th class="th" style="text-align:right">Nominal</th>
                    <th class="th">Sumber</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($rows ?? []) as $r)
                    <tr class="row">
                        <td class="td">{{ optional($r->tanggal)->format('Y-m-d') }}</td>
                        <td class="td">{{ $r->jenis }}</td>
                        <td class="td">{{ $r->metode }}</td>
                        <td class="td" style="text-align:right">Rp {{ number_format((float) $r->nominal, 0, ',', '.') }}</td>
                        <td class="td">{{ $r->sumber }}</td>
                    </tr>
                @empty
                    <tr class="row">
                        <td class="td" colspan="5" style="color:#6b7280">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</x-filament::page>
