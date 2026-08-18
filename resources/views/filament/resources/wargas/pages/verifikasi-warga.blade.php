<x-filament::page>
    <style>
        .seg {display:inline-flex; gap:16px; background:#f3f4f6; padding:8px 12px; border-radius:9999px; margin-bottom:12px}
        .seg a{font-size:13px; font-weight:600; color:#1f2937; text-decoration:none}
        .seg a:hover{color:#2563eb}
    </style>
    <div class="seg">
        <a href="{{ \App\Filament\Resources\Wargas\WargaResource::getUrl('index') }}">Data Warga</a>
        <a href="{{ \App\Filament\Resources\Wargas\WargaResource::getUrl('verifikasi') }}">Verifikasi Warga Baru</a>
    </div>
    {{ $this->table }}
</x-filament::page>
