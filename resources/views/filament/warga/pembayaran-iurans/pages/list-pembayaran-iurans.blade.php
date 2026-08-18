<x-filament::page>
    <style>
        .year-group{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:14px;overflow:hidden}
        .year-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e5e7eb;font-weight:600;color:#111827}
        .year-toggle{cursor:pointer;display:flex;align-items:center;gap:8px;color:#374151}
        .chev{transition:transform .2s ease}
        .chev.rot{transform:rotate(90deg)}
        .year-body{padding:0 12px 12px 12px}
        .table{width:100%;border-collapse:collapse;table-layout:fixed}
        .th,.td{padding:10px 8px;font-size:13px;color:#111827;text-align:center}
        .th{color:#374151}
        .row{border-top:1px solid #eef2f7}
        .badge{display:inline-block;padding:2px 8px;border-radius:9999px;font-size:12px}
        .badge-success{background:#e0f4fb;color:#2ea7d6}
        .badge-warning{background:#fef9c3;color:#854d0e}
        .badge-gray{background:#f3f4f6;color:#374151}
        .btn{background:#2ea7d6;color:#fff;padding:6px 10px;border-radius:8px;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;min-width:70px}
        .btn:hover{background:#238cb5}
        .btn-muted{background:#6b7280}
        .btn-muted:hover{background:#4b5563}
        .btn-disabled{background:#e5e7eb;color:#6b7280;cursor:not-allowed}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:9999}
        [x-cloak]{display:none !important;}
        @media (max-width: 900px) {
            .th,.td{font-size:12px;padding:8px 6px}
        }
    </style>

    @forelse(($years ?? []) as $yearGroup)
        <div class="year-group" x-data="{ open: true }">
            <div class="year-header">
                <div>{{ $yearGroup['year'] }}</div>
                <div class="year-toggle" @click="open = !open">
                    <svg class="chev" :class="{ 'rot': open }" width="16" height="16" viewBox="0 0 20 20" fill="none">
                        <path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
            <div class="year-body" x-show="open" x-cloak>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="th">Bulan</th>
                            <th class="th">Nominal</th>
                            <th class="th">Status</th>
                            <th class="th">Tanggal Lunas</th>
                            <th class="th">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($yearGroup['rows'] ?? []) as $r)
                            <tr class="row" x-data="{ buktiOpen: false }">
                                <td class="td">{{ $r['bulan'] ?? '-' }}</td>
                                <td class="td">{{ number_format((float) ($r['nominal'] ?? 0), 0, ',', '.') }}</td>
                                <td class="td">
                                    @php $s = strtolower((string) ($r['status'] ?? '')); @endphp
                                    <span class="badge {{ in_array($s, ['lunas', 'settlement'], true) ? 'badge-success' : ($s === 'belum bayar' ? 'badge-warning' : 'badge-gray') }}">
                                        {{ in_array($s, ['lunas', 'settlement'], true) ? 'Lunas' : ($r['status'] ?? '-') }}
                                    </span>
                                </td>
                                <td class="td">{{ !empty($r['tanggal_lunas']) ? \Illuminate\Support\Carbon::parse($r['tanggal_lunas'])->format('Y-m-d') : '-' }}</td>
                                <td class="td">
                                    <div style="display:inline-flex;gap:8px;flex-wrap:wrap;justify-content:center">
                                        @if(!empty($r['bukti_tunai']))
                                            <a class="btn btn-muted" href="#" @click.prevent="buktiOpen = true">Bukti</a>
                                            <div x-show="buktiOpen" x-transition.opacity class="modal-overlay" @click.self="buktiOpen = false">
                                                <div style="background:#fff;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.12);width:860px;max-width:95vw">
                                                    <div style="padding:14px 16px;border-bottom:1px solid #eee;font-weight:600;color:#111827">Bukti Pembayaran Tunai</div>
                                                    <div style="padding:12px;">
                                                        @if(!empty($r['penerima_tunai']))
                                                            <div style="font-size:13px;color:#374151;margin-bottom:10px;font-weight:500;text-align:left;">Diterima Oleh: {{ $r['penerima_tunai'] }}</div>
                                                        @endif
                                                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px">
                                                            @foreach((array) ($r['bukti_tunai'] ?? []) as $path)
                                                                @php $url = '/storage/'.ltrim((string) $path, '/'); @endphp
                                                                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden">
                                                                    <div style="padding:8px;display:flex;align-items:center;justify-content:center">
                                                                        <img src="{{ $url }}" alt="{{ basename((string) $path) }}" style="max-width:100%;max-height:240px;object-fit:contain;border-radius:8px">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div style="display:flex;gap:8px;justify-content:flex-end;padding:12px 16px;border-top:1px solid #eee">
                                                        <button type="button" @click="buktiOpen = false" style="background:#f3f4f6;color:#374151;padding:8px 10px;border-radius:8px;font-size:13px">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            @if(($r['can_pay'] ?? false) === true)
                                                <a class="btn" href="{{ route('payments.tagihan.bayar', ['tagihan' => $r['id'], 'panel' => 'warga']) }}">Bayar</a>
                                            @elseif(in_array($s, ['lunas', 'settlement'], true) || !empty($r['has_tunai']))
                                                <span class="btn btn-disabled">Bayar</span>
                                            @else
                                                <span class="btn btn-disabled" title="Silakan bayar bulan sebelumnya terlebih dahulu">Bayar</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="row">
                                <td class="td" colspan="5" style="color:#6b7280">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="year-group">
            <div class="year-header">
                <div>Tagihan Iuran</div>
            </div>
            <div class="year-body">
                <div style="color:#6b7280;padding:10px 4px">Tidak ada tagihan iuran.</div>
            </div>
        </div>
    @endforelse
</x-filament::page>
