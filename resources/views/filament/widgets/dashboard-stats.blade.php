<x-filament::widget>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.admin-dashboard-layout{display:flex;flex-direction:column;gap:18px;}
.top-section{display:grid;grid-template-columns:minmax(0,2.1fr) minmax(0,3fr);gap:16px;align-items:stretch;}
.welcome-card{background:#e0f4fb;border-radius:18px;padding:18px 18px 16px 18px;display:flex;flex-direction:column;justify-content:center;min-height:150px;}
.welcome-title{font-size:18px;font-weight:700;color:#111827;margin-bottom:4px;}
.welcome-sub{font-size:12px;color:#4b5563;line-height:1.5;max-width:260px;}
.welcome-highlight{color:#435663;font-weight:600;}
.welcome-footer{display:flex;align-items:center;justify-content:space-between;margin-top:14px;}
.welcome-button{display:inline-flex;align-items:center;gap:8px;background:#2ea7d6;color:#fff;border-radius:999px;padding:8px 14px;font-size:12px;font-weight:600;text-decoration:none;}
.welcome-button i{font-size:13px;}
.welcome-meta{font-size:11px;color:#6b7280;text-align:right;}
.summary-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
.summary-card{background:#fff;border-radius:14px;border:1px solid rgba(148,163,184,.35);padding:12px 13px;display:flex;flex-direction:column;gap:6px;box-shadow:0 4px 10px rgba(15,23,42,.04);position:relative;}
.summary-header{display:flex;justify-content:space-between;align-items:center;}
.summary-label{font-size:11px;color:#6b7280;font-weight:600;display:flex;align-items:center;gap:6px;}
.summary-icon{width:22px;height:22px;border-radius:999px;display:flex;align-items:center;justify-content:center;font-size:12px;}
.summary-value{font-size:18px;font-weight:700;color:#111827;}
.summary-desc{font-size:11px;color:#6b7280;display:flex;justify-content:space-between;align-items:center;}
.summary-tag{font-size:10px;padding:2px 8px;border-radius:999px;background:#e0f4fb;color:#2ea7d6;font-weight:600;}
.summary-badge-new{position:absolute;top:8px;right:10px;font-size:10px;padding:3px 8px;border-radius:999px;background:#2ea7d6;color:#fff;font-weight:600;}
.summary-change-up{font-size:11px;color:#2ea7d6;font-weight:600;}
.summary-change-down{font-size:11px;color:#dc2626;font-weight:600;}
.bottom-section{margin-top:4px;}
.chart-section{margin-top:12px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;}
.chart-card{  background:#fff; border-radius:14px;  border:1px solid rgba(148,163,184,.35);padding:12px 14px; box-shadow:0 4px 10px rgba(15,23,42,.04);display:flex; flex-direction:column;min-height:320px;}
.chart-header{display:flex;align-items:center;justify-content:space-between;}
.chart-title{font-size:13px;font-weight:600;color:#111827;}
.chart-meta{font-size:11px;color:#6b7280;text-align:right;}
.chart-total{font-size:11px;color:#4b5563;}
.chart-total span{margin-left:4px;font-weight:600;color:#111827;}
.chart-body{  position:relative;width:100%;  flex:1;  min-height:240px;  margin-top:6px;}
.fi-chart-widget{min-height:280px}
.fi-chart-widget .fi-card{height:100%}
.fi-chart-widget canvas{max-height:220px!important}
.pending-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;}
.pending-title{font-size:13px;font-weight:600;color:#111827;display:flex;align-items:center;gap:6px;}
.pending-count{font-size:11px;color:#2ea7d6;background:#e0f4fb;border-radius:999px;padding:2px 8px;}
.pending-link{font-size:11px;color:#2ea7d6;text-decoration:none;font-weight:500;}
.pending-list{background:#fff;border-radius:14px;border:1px solid rgba(148,163,184,.35);padding:8px 10px;display:flex;flex-direction:column;gap:6px;}
.pending-item{display:flex;align-items:flex-start;justify-content:space-between;padding:6px 4px;border-radius:10px;}
.pending-item-main{display:flex;align-items:flex-start;gap:8px;}
.avatar{width:30px;height:30px;border-radius:999px;background:rgba(67,86,99,.08);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#435663;}
.pending-name{font-size:13px;font-weight:600;color:#111827;}
.pending-meta{font-size:11px;color:#4b5563;}
.pending-meta span{margin-right:6px;}
.pending-status{font-size:11px;padding:3px 9px;border-radius:999px;background:#fef3c7;color:#92400e;font-weight:600;}
.pending-contact{font-size:11px;color:#4b5563;text-align:right;}
.pending-empty{font-size:12px;color:#6b7280;padding:4px 2px;}
@media (max-width:1024px){
  .top-section{grid-template-columns:1fr;}
}
@media (max-width:640px){
  .summary-grid{grid-template-columns:1fr 1fr;}
  .chart-section{grid-template-columns:1fr;}
}
</style>

@php
    $stats = $this->getStats();
    $findStat = function (string $label) use ($stats) {
        foreach ($stats as $stat) {
            if (strtolower($stat->getLabel()) === strtolower($label)) {
                return $stat;
            }
        }
        return null;
    };

    $statWargaAktif = $findStat('Warga Aktif');
    $statWargaVerif = $findStat('Warga Menunggu Verifikasi');
    $statPengaduanBaru = $findStat('Pengaduan Baru');
    $statSurat = $findStat('Surat Menunggu');
    $statIuran = $findStat('Iuran Bulan Ini');
    $statKas = $findStat('Arus Kas RT');

    $user = \Illuminate\Support\Facades\Auth::user();
    $nama = $user?->name ?? 'Admin';
    $role = $user?->role;
    $roleName = strtolower((string) ($role?->name ?? ''));

    $jabatan = 'Admin';
    if ($role?->isRW()) {
        if (str_contains($roleName, 'skre')) {
            $jabatan = 'Sekretaris RW';
        } elseif (str_contains($roleName, 'benda')) {
            $jabatan = 'Bendahara RW';
        } else {
            $jabatan = 'Ketua RW';
        }
    } elseif ($role?->isRT()) {
        if (str_contains($roleName, 'skre')) {
            $jabatan = 'Sekretaris RT';
        } elseif (str_contains($roleName, 'benda')) {
            $jabatan = 'Bendahara RT';
        } else {
            $jabatan = 'Ketua RT';
        }
    } elseif ($role?->isAdmin()) {
        $jabatan = 'Admin';
    }

    $rtId = ($role?->isRT()) ? ($user?->warga?->no_rt_id) : null;
    $rtNomor = $rtId ? (\App\Models\NoRt::find($rtId)?->nomor ?? null) : null;

    $rtLabel = null;
    if ($rtNomor !== null && $rtNomor !== '') {
        $rtLabel = 'RT '.str_pad((string) $rtNomor, 3, '0', STR_PAD_LEFT);
    } elseif ($rtId) {
        $rtLabel = str_starts_with((string) $rtId, 'RT-')
            ? str_replace('-', ' ', (string) $rtId)
            : 'RT '.str_pad((string) $rtId, 3, '0', STR_PAD_LEFT);
    }

    $greetingLabel = $jabatan;
    if ($rtLabel) {
        $greetingLabel .= ' '.$rtLabel.'';
    }

    $suratBaru = method_exists($this, 'getSuratNewCount') ? $this->getSuratNewCount() : 0;
    $iuranBaru = method_exists($this, 'getIuranNewCount') ? $this->getIuranNewCount() : 0;

    $pendingQuery = \App\Models\Warga::query()
        ->whereHas('user', function ($query) {
            $query->whereHas('role', function ($q) {
                $q->where('name', 'tamu');
            });
        });

    if ($rtId) {
        $pendingQuery->where('no_rt_id', $rtId);
    }

    $pendingTotal = $pendingQuery->count();
    $pendingWarga = $pendingQuery->orderByDesc('created_at')->limit(3)->get();

    $verifikasiUrl = \App\Filament\Resources\Wargas\WargaResource::getUrl('verifikasi');

@endphp

<div class="admin-dashboard-layout">
    <div class="top-section">
        <div class="welcome-card">
            <div>
                <div class="welcome-title">
                    Selamat Datang, {{ $greetingLabel }}
                </div>
                <div class="welcome-sub">
                    Ini adalah ringkasan aktivitas administrasi. Ada
                    <span class="welcome-highlight">{{ $pendingTotal }}</span>
                    verifikasi warga baru yang membutuhkan perhatian.
                </div>
            </div>

        </div>

        <div class="summary-grid">
            @if($statWargaAktif)
                <a href="{{ $statWargaAktif->getUrl() }}" style="text-decoration:none;color:inherit;">
                    <div class="summary-card">
                        <div class="summary-header">
                            <div class="summary-label">
                                <span class="summary-icon" style="background:#e0f4fb;color:#2ea7d6;">
                                    <i class="fa-solid fa-users"></i>
                                </span>
                                Warga Aktif
                            </div>
                        </div>
                        <div class="summary-value">{{ $statWargaAktif->getValue() }}</div>
                        <div class="summary-desc">
                            <span>Total warga terdaftar{{ $rtLabel ? ' di '.$rtLabel : '' }}</span>
                        </div>
                    </div>
                </a>
            @endif

            @if($statWargaVerif)
                <a href="{{ $statWargaVerif->getUrl() }}" style="text-decoration:none;color:inherit;">
                    <div class="summary-card">
                        <div class="summary-header">
                            <div class="summary-label">
                                <span class="summary-icon" style="background:#e0f4fb;color:#2ea7d6;">
                                    <i class="fa-solid fa-user-clock"></i>
                                </span>
                                Warga Menunggu
                            </div>
                        </div>
                        <div class="summary-value">{{ $statWargaVerif->getValue() }}</div>
                        <div class="summary-desc">
                            <span>Butuh verifikasi segera</span>
                            <span class="summary-tag">Baru</span>
                        </div>
                    </div>
                </a>
            @endif

            @if($statSurat)
                <a href="{{ $statSurat->getUrl() }}" style="text-decoration:none;color:inherit;">
                    <div class="summary-card">
                        @if($suratBaru > 0)
                            <div class="summary-badge-new">Baru {{ $suratBaru }}</div>
                        @endif
                        <div class="summary-header">
                            <div class="summary-label">
                                <span class="summary-icon" style="background:#e0f4fb;color:#2ea7d6;">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                Surat Menunggu
                            </div>
                        </div>
                        <div class="summary-value">{{ $statSurat->getValue() }}</div>
                        <div class="summary-desc">
                            <span>Butuh verifikasi segera</span>
                        </div>
                    </div>
                </a>
            @endif

            @if($statPengaduanBaru)
                <a href="{{ $statPengaduanBaru->getUrl() }}" style="text-decoration:none;color:inherit;">
                    <div class="summary-card">
                        <div class="summary-header">
                            <div class="summary-label">
                                <span class="summary-icon" style="background:#e0f4fb;color:#2ea7d6;">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </span>
                                Pengaduan Baru
                            </div>
                        </div>
                        <div class="summary-value">{{ $statPengaduanBaru->getValue() }}</div>
                        <div class="summary-desc">
                            <span>Pengaduan pending bulan ini</span>
                        </div>
                    </div>
                </a>
            @endif

            @if($statIuran && $statKas)
                <a href="{{ $statIuran->getUrl() }}" style="text-decoration:none;color:inherit;">
                    <div class="summary-card">
                        @if($iuranBaru > 0)
                            <div class="summary-badge-new">Baru {{ $iuranBaru }}</div>
                        @endif
                        <div class="summary-header">
                            <div class="summary-label">
                                <span class="summary-icon" style="background:#e0f4fb;color:#2ea7d6;">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                </span>
                                Iuran Bulan Ini
                            </div>
                        </div>
                        <div class="summary-value">{{ $statKas->getValue() }}</div>
                        <div class="summary-desc">
                            <span>Arus kas RT bulan ini</span>
                            @php($desc = $statKas->getDescription())
                            @if($desc)
                                <span class="{{ str_contains((string)$desc, '-') ? 'summary-change-down' : 'summary-change-up' }}">
                                    {!! $desc !!}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endif
        </div>
    </div>

    <div class="bottom-section">
        <div class="pending-header">
            <div class="pending-title">
                <span>Data Warga Menunggu Verifikasi</span>
                <span class="pending-count">{{ $pendingTotal }}</span>
            </div>
            <a href="{{ $verifikasiUrl }}" class="pending-link">Lihat Semua</a>
        </div>

        <div class="pending-list">
            @forelse($pendingWarga as $row)
                <div class="pending-item">
                    <div class="pending-item-main">
                        <div class="avatar">
                            {{ strtoupper(mb_substr($row->nama ?? 'W',0,1)) }}
                        </div>
                        <div>
                            <div class="pending-name">{{ $row->nama }}</div>
                            <div class="pending-meta">
                                <span>{{ \App\Models\Warga::maskedNik($row->warga_nik) }}</span>
                                @if($row->no_rt_id)
                                    <span>{{ $row->no_rt_id }}</span>
                                @endif
                                @if($row->rw)
                                    <span>RW {{ $row->rw }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="pending-contact">
                        @if($row->no_hp)
                            <div>{{ $row->no_hp }}</div>
                        @endif
                        <div class="pending-status">Menunggu</div>
                    </div>
                </div>
            @empty
                <div class="pending-empty">
                    Tidak ada warga yang menunggu verifikasi.
                </div>
            @endforelse
        </div>
    </div>
</div>

</x-filament::widget>
