<x-filament::page>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .rt-group{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:14px;overflow:hidden}
        .rt-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e5e7eb;font-weight:600;color:#111827}
        .rt-toggle{cursor:pointer;display:flex;align-items:center;gap:8px;color:#374151}
        .chev{transition:transform .2s ease}
        .chev.rot{transform:rotate(90deg)}
        .rt-body{padding:0 12px 12px 12px;margin-top: auto;}
        .table{width:100%;border-collapse:collapse;table-layout:fixed}
        .th,.td{padding:10px 8px;font-size:13px;color:#111827; text-align: center;}
        .th{color:#374151}
        .row{border-top:1px solid #eef2f7; }
        .badge{display:inline-block;padding:2px 8px;border-radius:9999px;font-size:12px}
        .badge-success{background:#e0f4fb;color:#2ea7d6}
        .badge-warning{background:#fef9c3;color:#854d0e}
        .badge-gray{background:#f3f4f6;color:#374151}
        .btn{background:#2ea7d6;color:#fff;padding:6px 10px;border-radius:8px;font-size:13px;text-decoration:none}
        .btn:hover{background:#238cb5}
        .modal-overlay { position: fixed; inset: 0;background: rgba(0,0,0,.35); display: flex; align-items: center; justify-content: center; z-index: 9999;}
        .rt-actions{display:flex;align-items:center;gap:8px}
        .sort-select{appearance:none;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;font-size:13px;color:#374151}
        .sort-select:focus{outline:none;border-color:#cbd5e1}
        .modal-card{background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,.16);width:480px;max-width:94vw}
        .modal-header{padding:18px 20px 10px 20px;text-align:center}
        .modal-body{padding:0 24px 20px 24px;text-align:center;color:#4b5563;font-size:14px}
        .modal-footer{padding:16px 20px 20px 20px;display:flex;justify-content:center;gap:10px}
        .modal-title{font-size:18px;font-weight:600;color:#111827;margin-top:8px}
        .modal-icon{width:40px;height:40px;border-radius:9999px;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto}
    </style>


    @foreach(($years ?? []) as $yearGroup)

        <div class="rt-group" x-data="{open:true}">
            <div class="rt-header">
                <div>{{ $yearGroup['year'] }}</div>
                <div class="rt-actions">
                <div class="rt-toggle" @click="open=!open">
                        <div style="display:flex;justify-content:flex-end;margin-bottom:12px;gap:8px">
                            <form method="get" style="display:flex;gap:8px;align-items:center">
                                <select name="no_rt_id" class="sort-select" onchange="this.form.submit()">
                                    <option value="">{{ __('Semua RT') }}</option>
                                    @foreach(($rtOptions ?? []) as $rid => $nomor)
                                        <option value="{{ $rid }}" {{ ($selectedRtId ?? null) == $rid ? 'selected' : '' }}>RT {{ $nomor }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <svg class="chev" :class="{ 'rot': open }" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 5l6 5-6 5" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
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
                                        <th class="th">Warga</th>
                                        <th class="th">Nominal Tagihan</th>
                                        <th class="th">Status</th>
                                        <th class="th">Tanggal Lunas</th>
                                        <th class="th">Edit</th>
                                        <th class="th">Bayar</th>
                                        <th class="th"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($g['rows'] as $r)
                                        <tr class="row" x-data="{deleteOpen:false}">
                                            <td class="td">{{ $r['warga'] }}</td>
                                            <td class="td">{{ number_format((float) $r['nominal'], 0, ',', '.') }}</td>
                                            <td class="td">
                                                @php $s = strtolower((string) $r['status']); @endphp
                                                <span class="badge {{ in_array($s,  ['lunas','settlement']) ? 'badge-success' : ($s === 'belum bayar' ? 'badge-warning' : 'badge-gray') }}">{{ in_array($s, ['lunas','settlement']) ? 'Lunas' : $r['status'] }}</span>
                                            </td>
                                            <td class="td">{{ $r['tanggal_lunas'] ? \Illuminate\Support\Carbon::parse($r['tanggal_lunas'])->format('Y-m-d') : '-' }}</td>
                                            <td class="td"><a href="{{ \App\Filament\Resources\TagihanIuranWargas\TagihanIuranWargaResource::getUrl('edit', ['record' => $r['id']]) }}" style="color:#2ea7d6;">Edit</a></td>
                                            <td class="td">
                                                @if(!in_array($s, ['lunas','settlement']) && empty($r['has_tunai']))
                                                    <div x-data="{cashOpen:false, buktiOpen:false, previews:[], nominal: '{{ (float) $r['nominal'] }}'}" style="display:inline-flex;gap:8px">
                                                        <a class="btn" href="{{ route('payments.tagihan.bayar', ['tagihan' => $r['id'], 'panel' => 'admin']) }}">Bayar</a>
                                                        <a class="btn" @click.prevent="cashOpen=true">Tunai</a>
                                                        @if(!empty($r['bukti_tunai']))
                                                            <a class="btn" @click.prevent="buktiOpen=true" style="background:#6b7280">Bukti</a>
                                                        @endif
                                                        <div x-show="cashOpen" x-transition.opacity class="modal-overlay"@click.self="cashOpen=false">
                                                            <div x-data="{previewId:''}" x-init="fetch('{{ route('payments.tagihan.tunai.preview', ['tagihan' => $r['id']]) }}').then(res=>res.json()).then(d=>previewId=d.next_id).catch(()=>{})" style="background:#fff;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.12);width:520px;max-width:92vw;max-height:80vh;overflow-y:auto">
                                                                <div style="padding:14px 16px;border-bottom:1px solid #eee;font-weight:600;color:#111827">Pembayaran Tunai</div>

                                                                <form method="post" action="{{ route('payments.tagihan.tunai', ['tagihan' => $r['id'], 'panel' => 'admin']) }}" enctype="multipart/form-data">
                                                                    @csrf
                                                                    <div style="padding:16px">
                                                                        <label style="display:block;font-size:13px;color:#374151;margin-bottom:6px">ID Transaksi</label>
                                                                        <input type="text" :value="previewId" readonly style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:14px;color:#111827;background:#f9fafb;margin-bottom:12px">
                                                                        <label style="display:block;font-size:13px;color:#374151;margin-bottom:6px">Bulan dibayarkan</label>
                                                                        <input type="text" value="{{ $g['bulan_label'] }}" readonly style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:14px;color:#111827;background:#f9fafb;margin-bottom:12px">
                                                                        <label style="display:block;font-size:13px;color:#374151;margin-bottom:6px">Nominal dibayarkan</label>
                                                                        <input name="nominal_dibayarkan" x-model="nominal" type="number" min="0" step="1" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:14px;color:#111827">
                                                                        <label style="display:block;font-size:13px;color:#374151;margin:12px 0 6px">Bukti (foto/berkas)</label>
                                                                        <input name="bukti[]" type="file" multiple @change="previews=[...$event.target.files].map(f=>URL.createObjectURL(f))" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:14px;color:#111827">
                                                                        <div x-show="previews.length>0" style="margin-top:10px;display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px">
                                                                            <template x-for="src in previews">
                                                                                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden">
                                                                                    <div style="padding:8px;display:flex;align-items:center;justify-content:center">
                                                                                        <img :src="src" style="max-width:100%;max-height:120px;object-fit:contain;border-radius:8px">
                                                                                    </div>
                                                                                </div>
                                                                            </template>
                                                                        </div>
                                                                        <label style="display:block;font-size:13px;color:#374151;margin:12px 0 6px">Penerima</label>
                                                                        <input type="text" value="{{ auth()->user()->name ?? '' }}" readonly style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:14px;color:#111827;background:#f9fafb">
                                                                    </div>
                                                                    <div style="display:flex;gap:8px;justify-content:flex-end;padding:12px 16px;border-top:1px solid #eee">
                                                                        <button type="button" @click="cashOpen=false" style="background:#f3f4f6;color:#374151;padding:8px 10px;border-radius:8px;font-size:13px">Batal</button>
                                                                        <button type="submit" style="background:#2ea7d6;color:#fff;padding:8px 12px;border-radius:8px;font-size:13px">Simpan</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        @if(!empty($r['bukti_tunai']))
                                                            <div x-show="buktiOpen" style="position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:50">
                                                                <div style="background:#fff;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.12);width:860px;max-width:95vw">
                                                                    <div style="padding:14px 16px;border-bottom:1px solid #eee;font-weight:600;color:#111827">Bukti Pembayaran Tunai</div>
                                                                    <div style="padding:12px">
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
                                                                        <button type="button" @click="buktiOpen=false" style="background:#f3f4f6;color:#374151;padding:8px 10px;border-radius:8px;font-size:13px">Tutup</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="td">
                                                <button type="button" @click.prevent="deleteOpen = true" style="background:none;border:none;color:#dc2626;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:4px" aria-label="Hapus tagihan" title="Hapus">
                                                    <i class="fa-solid fa-trash-can" style="font-size:16px;justify-content: center;"></i>
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
                                                            <div class="modal-title">Hapus Tagihan Iuran</div>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Apakah Anda yakin ingin menghapus tagihan ini? Tindakan ini tidak dapat dibatalkan.</p>
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
                                                                @click="$wire.deleteTagihan('{{ $r['id'] }}'); deleteOpen = false"
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
                @endforeach
            </div>
        </div>
    @endforeach
</x-filament::page>
