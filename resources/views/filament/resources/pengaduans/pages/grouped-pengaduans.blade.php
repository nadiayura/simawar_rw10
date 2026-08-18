<x-filament::page>
    <style>
        .rt-group{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:14px;overflow:hidden}
        .rt-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e5e7eb;font-weight:600;color:#111827}
        .rt-toggle{cursor:pointer;display:flex;align-items:center;gap:8px;color:#374151}
        .chev{transition:transform .2s ease}
        .chev.rot{transform:rotate(90deg)}
        .rt-body{padding:0 12px 12px 12px}
        .table{width:100%;border-collapse:collapse;table-layout:fixed}
        .th,.td{padding:10px 8px;font-size:13px;color:#111827}
        .th{color:#374151}
        .row{border-top:1px solid #eef2f7}
        .row.unread{background:#fffbeb}
        .badge{display:inline-block;padding:2px 8px;border-radius:9999px;font-size:12px}
        .badge-success{background:#dcfce7;color:#166534}
        .badge-warning{background:#fef9c3;color:#854d0e}
        .badge-gray{background:#f3f4f6;color:#374151}
        .btn{background:#10b981;color:#fff;padding:6px 10px;border-radius:8px;font-size:13px;text-decoration:none}
        .btn:hover{background:#059669}
        .filters-summary{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:12px}
        .summary-card{flex:1 1 160px;background:#fff;border-radius:12px;padding:10px 14px;box-shadow:0 1px 4px rgba(0,0,0,.05);position:relative}
        .summary-label{font-size:11px;color:#6b7280;margin-bottom:4px}
        .summary-value{font-size:18px;font-weight:700;color:#111827}
        .summary-tooltip{position:absolute;left:0;top:100%;margin-top:6px;background:#111827;color:#e5e7eb;padding:8px 10px;border-radius:8px;font-size:11px;box-shadow:0 8px 16px rgba(15,23,42,.25);min-width:200px;z-index:50;display:none;white-space:pre-line}
        .summary-card:hover .summary-tooltip{display:block}
        .filters{display:flex;align-items:center;gap:8px;margin-bottom:12px}
        .filter-btn{border:1px solid #d1d5db;border-radius:9999px;padding:6px 10px;font-size:13px;background:#fff;color:#111827}
        .filter-btn:hover{background:#f9fafb}
        .filter-btn.active{background:#10b981;color:#fff;border-color:#10b981}
    </style>

    <div class="filters-summary">
        <div class="summary-card">
            <div class="summary-label">Total Pengaduan Diajukan</div>
            <div class="summary-value">{{ number_format($summary['total'] ?? 0) }}</div>
            <div class="summary-tooltip">
@php
    $detailTotal = $summary['detail']['total'] ?? [];
@endphp
@foreach($detailTotal as $jenis => $jumlah)
{{ $jumlah }} pengaduan {{ $jenis }}
@endforeach
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Pengaduan Selesai</div>
            <div class="summary-value text-green-600">{{ number_format($summary['selesai'] ?? 0) }}</div>
            <div class="summary-tooltip">
@php
    $detailSelesai = $summary['detail']['selesai'] ?? [];
@endphp
@foreach($detailSelesai as $jenis => $jumlah)
{{ $jumlah }} pengaduan {{ $jenis }}
@endforeach
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Pengaduan Belum Selesai</div>
            <div class="summary-value text-amber-600">{{ number_format($summary['belum_selesai'] ?? 0) }}</div>
            <div class="summary-tooltip">
@php
    $detailBelum = $summary['detail']['belum_selesai'] ?? [];
@endphp
@foreach($detailBelum as $jenis => $jumlah)
{{ $jumlah }} pengaduan {{ $jenis }}
@endforeach
            </div>
        </div>
    </div>

    <div class="filters" x-data="{showRange:false}">
        <a class="filter-btn{{ request()->query('range')==='month' ? ' active' : '' }}" href="?range=month">Bulan Ini</a>
        <a class="filter-btn{{ request()->query('range')==='week' ? ' active' : '' }}" href="?range=week">Minggu Ini</a>
        <a class="filter-btn{{ request()->query('range')==='year' ? ' active' : '' }}" href="?range=year">Tahun Ini</a>
        <button type="button" class="filter-btn{{ (request()->query('start') && request()->query('end')) ? ' active' : '' }}" @click="showRange=!showRange">Filter Tanggal</button>
        <form method="get" x-show="showRange" style="display:flex;align-items:center;gap:8px">
            <input type="date" name="start" />
            <span>s/d</span>
            <input type="date" name="end" />
            <button class="btn" type="submit">Terapkan</button>
        </form>
    </div>

    @foreach($groups as $g)
        <div class="rt-group" x-data="{open:true}">
            <div class="rt-header">
                <div>{{ $g['rt_label'] }}</div>
                <div class="rt-toggle" @click="open=!open">
                    <svg class="chev" :class="{ 'rot': open }" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
            <div class="rt-body" x-show="open">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="th">Nama Warga</th>
                            <th class="th">Jenis Pengaduan</th>
                            <th class="th">Judul</th>
                            <th class="th">Tgl Pengajuan</th>
                            <th class="th">Status</th>
                            <th class="th">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($g['rows'] as $r)
                            <tr class="row" data-id="{{ $r['id'] }}">
                                <td class="td">{{ $r['warga'] }}</td>
                                <td class="td">{{ $r['jenis'] }}</td>
                                <td class="td">{{ $r['judul'] }}</td>
                                <td class="td">{{ $r['tgl_pengajuan'] ? \Illuminate\Support\Carbon::parse($r['tgl_pengajuan'])->format('Y-m-d') : '-' }}</td>
                                <td class="td">
                                    @php $s = strtolower((string) $r['status']); @endphp
                                    <span class="badge {{ $s === 'selesai' ? 'badge-success' : ($s === 'pending' ? 'badge-warning' : 'badge-gray') }}">{{ $r['status'] }}</span>
                                </td>
                                <td class="td">
                                    <a href="{{ \App\Filament\Resources\Pengaduans\PengaduanResource::getUrl('view', ['record' => $r['id']]) }}" onclick="markViewed('pengaduan','{{ $r['id'] }}')" style="color:#2ea7d6;">Lihat</a>
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
    @endforeach
    <script>
        (function(){
            function getSet(key){
                try { return new Set(JSON.parse(localStorage.getItem(key) || '[]')); }
                catch(e){ return new Set(); }
            }
            function saveSet(key, set){
                localStorage.setItem(key, JSON.stringify(Array.from(set)));
            }
            window.markViewed = function(type, id){
                var key = type === 'pengaduan' ? 'viewed_pengaduan_ids' : 'viewed_suket_ids';
                var set = getSet(key);
                set.add(String(id));
                saveSet(key, set);
            };
            var set = getSet('viewed_pengaduan_ids');
            document.querySelectorAll('tr.row[data-id]').forEach(function(tr){
                var id = tr.getAttribute('data-id');
                if (!set.has(String(id))) tr.classList.add('unread');
            });
        })();
    </script>
</x-filament::page>
