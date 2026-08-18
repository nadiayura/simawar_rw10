<x-filament::page>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        .badge{display:inline-block;padding:2px 8px;border-radius:9999px;font-size:12px}
        .badge-success{background:#dcfce7;color:#166534}
        .badge-warning{background:#fef9c3;color:#854d0e}
        .badge-gray{background:#f3f4f6;color:#374151}
        .btn{background:#2ea7d6;color:#fff;padding:6px 10px;border-radius:10px;font-size:13px;text-decoration:none;white-space:nowrap}
        .btn:hover{background:#2ea7d6}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:9999}
        .modal-card{background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,.16);width:480px;max-width:94vw}
        .modal-header{padding:18px 20px 10px 20px;text-align:center}
        .modal-body{padding:0 24px 20px 24px;text-align:center;color:#4b5563;font-size:14px}
        .modal-footer{padding:16px 20px 20px 20px;display:flex;justify-content:center;gap:10px}
        .modal-title{font-size:18px;font-weight:600;color:#111827;margin-top:8px}
        .modal-icon{width:40px;height:40px;border-radius:9999px;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto}
    </style>

    @php
        $user = auth()->user();
        $canManage = $user
            && $user->role
            && (
                $user->role->isAdmin()
                || $user->role->isRW()
                || ($user->role->isRT() && strtolower((string) $user->role->name) !== 'rt')
            );

        $years = [];
        foreach ($groups as $g) {
            $label = (string) ($g['bulan_label'] ?? '');
            $parts = preg_split('/\s+/', $label);
            $yearPart = (int) (array_pop($parts) ?? now()->year);
            $monthLabel = trim(implode(' ', $parts)) ?: $label;

            if (! isset($years[$yearPart])) {
                $years[$yearPart] = [
                    'year' => $yearPart,
                    'months' => [],
                ];
            }

            $years[$yearPart]['months'][] = [
                'bulan_label' => $monthLabel.' '.$yearPart,
                'rows' => $g['rows'] ?? [],
            ];
        }
        krsort($years);
        $years = array_values($years);
    @endphp

    @foreach(($years ?? []) as $yearGroup)
        <div class="rt-group" x-data="{open:true}">
            <div class="rt-header">
                <div>{{ $yearGroup['year'] }}</div>
                <div class="rt-toggle" @click="open=!open">
                    <svg class="chev" :class="{ 'rot': open }" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
            <div class="rt-body" x-show="open">
                @foreach(($yearGroup['months'] ?? []) as $g)
                    <div x-data="{openMonth:true}" style="margin-bottom:12px; margin-top: 12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                            <div style="font-weight:600">{{ $g['bulan_label'] }}</div>
                            <div class="rt-toggle" @click="openMonth=!openMonth">
                                <svg class="chev" :class="{ 'rot': openMonth }" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                        <div x-show="openMonth">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="th">Jenis Kegiatan</th>
                                        <th class="th">Nama Kegiatan</th>
                                        <th class="th">Tanggal</th>
                                        <th class="th">Penanggung Jawab</th>
                                        <th class="th">Status</th>
                                        @if($canManage)
                                            <th class="th">Edit</th>
                                            <th class="th">Hapus</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($g['rows'] as $r)
                                        <tr class="row" x-data="{ deleteOpen:false }">
                                            <td class="td">{{ $r['jenis_kegiatan'] }}</td>
                                            <td class="td">{{ $r['nama_kegiatan'] }}</td>
                                            <td class="td">{{ $r['tanggal'] }}</td>
                                            <td class="td">{{ $r['penanggung_jawab'] ?? '-' }}</td>
                                            <td class="td">
                                                @php $s = strtolower((string) ($r['status'] ?? '')); @endphp
                                                <span class="badge {{ $s === 'selesai' ? 'badge-success' : ($s === 'dijadwalkan' || $s === 'terjadwal' || $s === 'berlangsung' ? 'badge-warning' : 'badge-gray') }}">
                                                    {{ $r['status'] ?? '-' }}
                                                </span>
                                            </td>
                                            @if($canManage)
                                                <td class="td">
                                                    <a href="{{ \App\Filament\Resources\KegKesehatans\KegKesehatanResource::getUrl('edit', ['record' => $r['id']]) }}" class="btn">
                                                        {{ $r['edit_label'] ?? 'Edit' }}
                                                    </a>
                                                </td>
                                                <td class="td">
                                                    <button type="button" @click.prevent="deleteOpen = true" style="background:none;border:none;color:#dc2626;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:4px" aria-label="Hapus kegiatan kesehatan" title="Hapus">
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
                                                                <div class="modal-title">Hapus Kegiatan Kesehatan</div>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Apakah Anda yakin ingin menghapus kegiatan ini? Tindakan ini tidak dapat dibatalkan.</p>
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
                                                                    @click="$wire.deleteKegiatan('{{ $r['id'] }}'); deleteOpen = false"
                                                                    style="background:#dc2626;color:#fff;padding:8px 18px;border-radius:9999px;font-size:13px;font-weight:500;min-width:96px"
                                                                >
                                                                    Hapus
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr class="row">
                                            <td class="td" colspan="{{ $canManage ? 7 : 5 }}" style="color:#6b7280">Tidak ada data.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</x-filament::page>
