<x-filament::page>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .seg{display:inline-flex;gap:16px;background:#f3f4f6;padding:8px 12px;border-radius:9999px;margin-bottom:12px}
        .rt-group{border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px;overflow:hidden}
        .rt-header{display:flex;align-items:center;justify-content:space-between;background:#f9fafb;padding:10px 12px;border-bottom:1px solid #e5e7eb}
        .rt-toggle{cursor:pointer;display:flex;align-items:center}
        .chev{transition:transform .2s ease}
        .rot{transform:rotate(90deg)}
        .rt-body{padding:12px}
        .table{width:100%;border-collapse:collapse;table-layout:fixed}
        .th,.td{padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:left;font-size:14px}
        .th{color:#374151;font-weight:600;background:#f9fafb}
        .row:last-child .td{border-bottom:none}
        .badge{display:inline-block;padding:2px 8px;border-radius:9999px;font-size:12px;white-space:nowrap}
        .badge-success{background:#dcfce7;color:#166534}
        .badge-info{background:#e0f2fe;color:#075985}
        .badge-warning{background:#fef9c3;color:#854d0e}
        .badge-gray{background:#f3f4f6;color:#374151}
        .btn-add{background:#2ea7d6;color:#fff;padding:6px 10px;border-radius:8px;font-size:13px;text-decoration:none}
        .btn-add:hover{background:#2ea7d6}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:9999}
        .modal-card{background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,.16);width:480px;max-width:94vw}
        .modal-header{padding:18px 20px 10px 20px;text-align:center}
        .modal-body{padding:0 24px 20px 24px;text-align:center;color:#4b5563;font-size:14px}
        .modal-footer{padding:16px 20px 20px 20px;display:flex;justify-content:center;gap:10px}
        .modal-title{font-size:18px;font-weight:600;color:#111827;margin-top:8px}
        .modal-icon{width:40px;height:40px;border-radius:9999px;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto}
    </style>

    @foreach($groups as $g)
        <div class="rt-group" x-data="{open:true}">
            <div class="rt-header">
                <div>{{ $g['rt_label'] }}</div>
                <div style="display:flex;align-items:center;gap:8px">
                    @php $user = auth()->user(); $showCreate = $user && $user->role && ! $user->role->isRT(); @endphp
                    @if($showCreate)
                        <a class="btn-add" href="{{ \App\Filament\Resources\KetuaRts\KetuaRtResource::getUrl('create') . '?no_rt_id=' . $g['rt_id'] }}">Tambah Struktural</a>
                    @endif
                    <div class="rt-toggle" @click="open=!open">
                        <svg class="chev" :class="{ 'rot': open }" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
            </div>
            <div class="rt-body" x-show="open">
                <table class="table">
                    <thead>
                        <tr class="row">
                            <th class="th">Jabatan</th>
                            <th class="th">Nama</th>
                            <th class="th">Alamat</th>
                            <th class="th">No HP</th>
                            <th class="th">Periode</th>
                            <th class="th">Aktif</th>
                            <th class="th">Edit</th>
                            <th class="th"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($g['rows'] as $r)
                            <tr class="row" x-data="{ deleteOpen:false }">
                                <td class="td">
                                    <span class="badge {{ match($r['jabatan']) {
                                        'Ketua RT' => 'badge-success',
                                        'Sekretaris RT' => 'badge-info',
                                        'Bendahara RT' => 'badge-warning',
                                        default => 'badge-gray',
                                    } }}">{{ $r['jabatan'] }}</span>
                                </td>
                                <td class="td">{{ $r['nama'] }}</td>
                                <td class="td">{{ $r['alamat'] }}</td>
                                <td class="td">{{ $r['no_hp'] }}</td>
                                <td class="td">{{ $r['periode'] }}</td>
                                <td class="td">{{ $r['is_active'] ? 'Ya' : 'Tidak' }}</td>
                                <td class="td"><a href="{{ \App\Filament\Resources\KetuaRts\KetuaRtResource::getUrl('edit', ['record' => $r['id']]) }}" style="color:#2563eb;">Edit</a></td>
                                <td class="td">
                                    <button type="button" @click.prevent="deleteOpen = true" style="background:none;border:none;color:#dc2626;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:4px" aria-label="Hapus struktural" title="Hapus">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <div x-show="deleteOpen" x-transition.opacity class="modal-overlay" @click.self="deleteOpen = false">
                                        <div class="modal-card">
                                            <div class="modal-header">
                                                <div class="modal-icon">
                                                    <i class="fa-solid fa-trash-can" style="color:#dc2626;font-size:20px;"></i>
                                                </div>
                                                <div class="modal-title">Hapus Struktural RT</div>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menghapus data struktural ini? Tindakan ini tidak dapat dibatalkan.</p>
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
                                                    @click="$wire.deleteStruktural('{{ $r['id'] }}'); deleteOpen = false"
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
                                <td class="td" colspan="7" style="color:#6b7280">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</x-filament::page>
