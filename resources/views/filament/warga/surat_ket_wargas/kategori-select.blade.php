<x-filament::page>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
.header{font-size:22px;font-weight:700;margin-bottom:6px;text-align:center}
.sub{font-size:13px;color:#6b7280;margin-bottom:5px;text-align:center}
.list {display: grid;grid-template-rows: repeat(2, auto);grid-auto-flow: column;gap: 12px;}
.item{background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:16px;display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.item-link {text-decoration: none;color: inherit;}
.item-link:hover {border-color: #6366f1;background: #f5f7ff;cursor: pointer;}
.item-link:active {transform: scale(0.99);}
.item-main{display:flex;align-items:flex-start;gap:12px}
.icon{font-size: 20px; color: #6366f1;}
.item-title{font-size:14px;font-weight:600;color:#111827}
.item-desc{font-size:12px;color:#6b7280;margin-top:2px}
.item-action a{display:inline-flex;align-items:center;gap:6px;background:#636CCB;color:#fff;padding:6px 10px;border-radius:9999px;font-size:12px;text-decoration:none}
.footer-link{display:flex;justify-content:center;margin-top:16px}
.footer-link a{background:#636CCB;color:#fff;padding:10px 14px;border-radius:10px;font-weight:600;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
@media (max-width: 768px) {
.list {grid-template-rows: none;grid-auto-flow: row;}}
</style>
<div class="sub">Silakan pilih jenis surat yang ingin diajukan.</div>
<div class="list">
@forelse($jenis as $js)
    <a href="{{ $createUrl }}?jenis={{ $js['jenis_surat_id'] }}" class="item item-link">
        <div class="item-main">
            <div class="icon">
                <i class="fa-solid fa-file-lines"></i>
            </div>
            <div>
                <div class="item-title">{{ $js['nama_surat'] }}</div>
                @if(!empty($js['deskripsi']))
                    <div class="item-desc">{{ $js['deskripsi'] }}</div>
                @endif
            </div>
        </div>
    </a>
@empty
    <div class="item">
        <div class="item-title">Belum ada jenis surat yang tersedia.</div>
    </div>
@endforelse
</div>


<div class="footer-link">
    <a href="{{ $listUrl }}">
        <i class="fa-solid fa-list"></i>
        <span>Lihat Surat Keterangan Saya</span>
    </a>
</div>
</x-filament::page>
