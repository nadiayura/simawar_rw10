<x-filament::page>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .pi-group{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:14px;overflow:hidden}
        .pi-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e5e7eb;font-weight:600;color:#111827}
        .pi-toggle{cursor:pointer;display:flex;align-items:center;gap:8px;color:#374151}
        .chev{transition:transform .2s ease}
        .chev.rot{transform:rotate(90deg)}
        .pi-body{padding:0 12px 12px 12px}
        .table{width:100%;border-collapse:collapse;table-layout:auto}
        .th,.td{padding:10px 8px;font-size:13px;color:#111827;white-space:normal;word-break:break-word;overflow:visible}
        .th{color:#374151}
        .row{border-top:1px solid #eef2f7}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:9999}
.modal-card{background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,.16);width:480px;max-width:94vw;overflow:hidden}
.modal-header{padding:22px 20px 12px 20px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:8px}
.modal-body{padding:0 24px 20px 24px;text-align:center;color:#4b5563;font-size:14px;white-space:normal;word-break:break-word;overflow-wrap:anywhere}
.modal-footer{padding:16px 20px 20px 20px;display:flex;justify-content:center;gap:10px}
.modal-title{font-size:18px;font-weight:600;color:#111827;margin-top:8px}
.modal-icon{width:40px;height:40px;border-radius:9999px;background:#fee2e2;display:inline-flex;align-items:center;justify-content:center;margin:0 auto}
        .btn{background:#10b981;color:#fff;padding:6px 10px;border-radius:8px;font-size:13px;text-decoration:none}
        .btn:hover{background:#059669}
        .action-link{color:#2ea7d6;text-decoration:none}
    </style>

    @foreach(($years ?? []) as $y)
        <div class="pi-group" x-data="{open:true}">
            <div class="pi-header">
                <div>Tahun {{ $y['year'] }}</div>
                <div class="pi-toggle" @click="open=!open">
                    <svg class="chev" :class="{ 'rot': open }" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
            <div class="pi-body" x-show="open">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="th">Bulan</th>
                            <th class="th">Tanggal Jatuh Tempo</th>
                            <th class="th" style="text-align:center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($y['rows'] as $r)
                            <tr class="row" x-data="{ deleteOpen:false }">
                                <td class="td">{{ $r['bulan'] }}</td>
                                <td class="td">{{ $r['jatuh_tempo'] }}</td>
                                <td class="td" style="padding:10px 8px">
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <a href="{{ \App\Filament\Resources\PeriodeIurans\PeriodeIuranResource::getUrl('edit', ['record' => $r['id']]) }}" style="color:#2ea7d6">Edit</a>
                                        <button type="button" @click.prevent="deleteOpen = true" style="background:none;border:none;color:#dc2626;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:4px" aria-label="Hapus tagihan" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>

                                    <template x-teleport="body">
                                        <div x-show="deleteOpen" x-transition.opacity class="modal-overlay" @click.self="deleteOpen = false" >
                                            <div class="modal-card">
                                                <div class="modal-header">
                                                    <div class="modal-icon">
                                                        <i class="fa-solid fa-trash-can" style="color:#dc2626;font-size:20px;"></i>
                                                    </div>
                                                    <div class="modal-title">Hapus Periode</div>
                                                </div>
                                                <div class="modal-body">
                                                    Apakah Anda yakin ingin menghapus periode ini? Tindakan ini tidak dapat dibatalkan.
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
                                                        @click="$wire.deletePeriode('{{ $r['id'] }}'); deleteOpen = false"
                                                        style="background:#dc2626;color:#fff;padding:8px 18px;border-radius:9999px;font-size:13px;font-weight:500;min-width:96px"
                                                    >
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </td>
                            </tr>
                        @empty
                            <tr class="row">
                                <td class="td" colspan="3" style="color:#6b7280">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</x-filament::page>
