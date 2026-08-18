<x-filament::page>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .group{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:14px;overflow:hidden}
        .group-h{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e5e7eb;font-weight:600;color:#111827}
        .group-b{padding:0 12px 12px 12px}
        .table{width:100%;border-collapse:collapse;table-layout:fixed}
        .th,.td{padding:10px 8px;font-size:13px;color:#111827}
        .th{color:#374151}
        .row{border-top:1px solid #eef2f7}
        .badge{display:inline-block;padding:2px 8px;border-radius:9999px;font-size:12px}
        .badge-green{background:#dcfce7;color:#166534}
        .badge-gray{background:#e5e7eb;color:#374151}
        .action a{color:#A3B087;text-decoration:none}
        .cta a{display:inline-flex;align-items:center;gap:6px;background:#A3B087;color:#fff;padding:6px 10px;border-radius:8px;font-size:13px;text-decoration:none}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:9999}
        .modal-card{background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,.16);width:480px;max-width:94vw}
        .modal-header{padding:18px 20px 10px 20px;text-align:center}
        .modal-body{padding:0 24px 20px 24px;text-align:center;color:#4b5563;font-size:14px}
        .modal-footer{padding:16px 20px 20px 20px;display:flex;justify-content:center;gap:10px}
        .modal-title{font-size:18px;font-weight:600;color:#111827;margin-top:8px}
        .modal-icon{width:40px;height:40px;border-radius:9999px;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto}
    </style>
    <div class="cta" style="margin-bottom:12px">
        <a href="{{ \App\Filament\Resources\JenisSurats\JenisSuratResource::getUrl('create') }}">Tambah Jenis Surat</a>
    </div>
    @foreach($groups as $g)
        <div class="group">
            <div class="group-h">
                <div>{{ $g['label'] }}</div>
            </div>
            <div class="group-b">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="th">Nama Jenis Surat</th>
                            <th class="th">Status</th>
                            <th class="th">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($g['items'] as $item)
                            <tr class="row" x-data="{ deleteOpen:false }">
                                <td class="td">{{ $item['nama'] }}</td>
                                <td class="td">
                                    <span class="badge {{ $item['aktif'] ? 'badge-green' : 'badge-gray' }}">{{ $item['aktif'] ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td class="td action" style="display:flex;align-items:center;gap:8px">
                                    <a href="{{ \App\Filament\Resources\JenisSurats\JenisSuratResource::getUrl('edit', ['record' => $item['id']]) }}">Edit</a>
                                    <button type="button" @click.prevent="deleteOpen = true" style="background:none;border:none;color:#dc2626;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:4px" aria-label="Hapus jenis surat" title="Hapus">
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
                                                <div class="modal-title">Hapus Jenis Surat</div>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menghapus jenis surat ini? Tindakan ini tidak dapat dibatalkan.</p>
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
                                                    @click="$wire.deleteJenisSurat('{{ $item['id'] }}'); deleteOpen = false"
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
                                <td class="td" colspan="3" style="color:#6b7280">Belum ada jenis surat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</x-filament::page>
