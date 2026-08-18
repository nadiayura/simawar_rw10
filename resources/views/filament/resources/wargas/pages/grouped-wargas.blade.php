<x-filament::page>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
.seg {display:inline-flex; gap:16px; background:#f3f4f6; padding:8px 12px; border-radius:9999px; margin-bottom:12px}
.seg a{font-size:13px; font-weight:600; color:#1f2937; text-decoration:none}
.seg a:hover{color:#2ea7d6}
.rt-group{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:14px;overflow:hidden}
.rt-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e5e7eb;font-weight:600;color:#111827}
.rt-toggle{cursor:pointer;display:flex;align-items:center;gap:8px;color:#374151}
.chev{transition:transform .2s ease}
.chev.rot{transform:rotate(90deg)}
.rt-body{padding:0 12px 12px 12px}
.table{width:100%;border-collapse:collapse;table-layout:auto}
.th,.td{padding:10px 8px;font-size:13px;color:#111827;white-space:normal;word-break:break-word;overflow:visible}
.th{color:#374151}
.row{border-top:1px solid #eef2f7}
.btn-add{background:#2ea7d6;color:#fff;padding:6px 10px;border-radius:8px;font-size:13px;text-decoration:none}
.btn-add:hover{background:#238cb5}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:9999}
.modal-card{background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,.16);width:480px;max-width:94vw;overflow:hidden}
.modal-header{padding:22px 20px 12px 20px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:8px}
.modal-body{padding:0 24px 20px 24px;text-align:center;color:#4b5563;font-size:14px;white-space:normal;word-break:break-word;overflow-wrap:anywhere}
.modal-footer{padding:16px 20px 20px 20px;display:flex;justify-content:center;gap:10px}
.modal-title{font-size:18px;font-weight:600;color:#111827;margin-top:8px}
.modal-icon{width:40px;height:40px;border-radius:9999px;background:#fee2e2;display:inline-flex;align-items:center;justify-content:center;margin:0 auto}
@media (max-width:640px){.th,.td{font-size:12px}}
</style>
@foreach(($groups ?? []) as $g)
    @if(!empty($pending) && $loop->first)
        <div class="rt-group" x-data="{open:true}">
            <div class="rt-header">
                <div>Warga Belum Verifikasi</div>
                <div class="rt-toggle" @click="open=!open">
                    <svg class="chev" :class="{ 'rot': open }" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
            <div class="rt-body" x-show="open">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="th">NIK</th>
                            <th class="th">Nama</th>
                            <th class="th">Status Tinggal</th>
                            <th class="th">RT</th>
                            <th class="th">No HP</th>
                            <th class="th">Iuran</th>
                            <th class="th"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pending as $r)
                            <tr class="row">
                                <td class="td">{{ $r['nik'] }}</td>
                                <td class="td">{{ $r['nama'] }}</td>
                                <td class="td">{{ $r['status'] }}</td>
                                <td class="td">{{ $r['rt'] }}</td>
                                <td class="td">{{ $r['no_hp'] }}</td>
                                <td class="td">{{ $r['iuran'] }}</td>
                                <td class="td"><a href="{{ \App\Filament\Resources\Wargas\WargaResource::getUrl('view', ['record' => $r['nik']]) }}" style="color:#2ea7d6;">View</a></td>
                            </tr>
                        @empty
                            <tr class="row">
                                <td class="td" colspan="8" style="color:#6b7280">Tidak ada warga baru untuk diverifikasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    <div class="rt-group" x-data="{open:true}">
        <div class="rt-header">
            <div>{{ $g['rt_label'] }}</div>
            <div style="display:flex;align-items:center;gap:8px">
                <a class="btn-add" href="{{ \App\Filament\Resources\Wargas\WargaResource::getUrl('create') . '?no_rt_id=' . $g['rt_id'] }}">Tambah Warga</a>
                <div class="rt-toggle" @click="open=!open">
                    <svg class="chev" :class="{ 'rot': open }" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </div>
        <div class="rt-body" x-show="open">
            <table class="table">
                <thead>
                    <tr>
                        <th class="th">NIK</th>
                        <th class="th">Nama</th>
                        <th class="th">Status Tinggal</th>
                        <th class="th">RT</th>
                        <th class="th">No HP</th>
                        <th class="th">Iuran</th>
                        <th class="th"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($g['rows'] as $r)
                        <tr class="row" x-data="{ deleteOpen:false }">
                            <td class="td">{{ \App\Models\Warga::maskedNik($r['nik']) }}</td>
                            <td class="td">{{ $r['nama'] }}</td>
                            <td class="td">{{ $r['status'] }}</td>
                            <td class="td">{{ $r['rt'] }}</td>
                            <td class="td">{{ $r['no_hp'] }}</td>
                            <td class="td">{{ $r['iuran'] }}</td>
                            <td class="td">
                                <div style="display:flex;align-items:center;gap:8px">
                                    <a href="{{ \App\Filament\Resources\Wargas\WargaResource::getUrl('edit', ['record' => $r['nik']]) }}" style="color:#2ea7d6;">Edit</a>
                                    <button type="button" @click.prevent="deleteOpen = true" style="background:none;border:none;color:#dc2626;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:4px" aria-label="Hapus data warga" title="Hapus">
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
                                                <div class="modal-title">Hapus Data Warga</div>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menghapus data warga ini? Tindakan ini tidak dapat dibatalkan.</p>
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
                                                    @click="$wire.deleteWarga('{{ $r['nik'] }}'); deleteOpen = false"
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
                            <td class="td" colspan="6" style="color:#6b7280">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach
</x-filament::page>
