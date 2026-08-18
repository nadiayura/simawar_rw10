
<x-filament::widget>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
.dashboard-layout{
    display:flex;
    gap:16px;
    align-items:stretch;
    margin-top:16px;
}
.dashboard-left{

    flex:2;
}
.dashboard-center{
    flex:1;
    display:flex;
    background-color: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    padding: 16px;
    justify-content: center;
}
.dashboard-right{
    margin-top: 16px;
    flex:1;
    display:flex;
}
.overview {
    display: grid;
    grid-template-columns: repeat(2, minmax(220px, 1fr));
    gap: 12px;
    align-items: stretch;
}
.card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    padding: 16px;
    display: flex;
}
.card-inner {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
}
.label {
    color:#6b7280;
    margin-top:10px ;
    margin-bottom: 10px;
    font-size:11px;
    display:flex;
    align-items:center;
    gap:6px;
}
.value {
    font-size:16px;
    font-weight:700;
    color:#111827;
}
.desc {
    font-size:12px;
    color:#4b5563;
    margin-top:10px;
    line-height: 1.4;
}

.desc_keg {
    font-size:12px;
    font-weight:500;
    color: #000;
    margin-top:10px;
    line-height: 1.4;
}
.icon{ display:flex; justify-content:flex-end; margin-top:auto; }

.success {
    color:#16a34a;
}
.warning {
    color:#d97706;
}
.danger {
    color:#dc2626;
}

.title-overview{
    margin-bottom: 16px;

}

.title-overview h1 {
    font-size: 26px;
    font-weight: bold;
}

.title-overview p {
    margin-bottom: 8px;
    font-size: 12px;
    color:#6b7280;
}

.fi-header h1.fi-header-heading {
    display: none !important;
}

.btn-action {
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-bottom: 16px;
}
.btn-pengaduan, .btn-surat, .btn-iuran {
    padding:8px 16px;
    border-radius:10px;
    font-size:14px;
    font-weight:500;
    color:#fff;
    display:flex;
    align-items:center;
    gap:6px;
}
.btn-pengaduan {
    background:#636CCB ;
}
.btn-surat {
    background:#636CCB ;
}
.btn-iuran {
    color:#fff;
    background:#636CCB ;
}

.tracking-title { font-size:16px; font-weight:600; margin-bottom:8px; margin-top:16px; color:#111827; }
.tracking-wrapper { margin-top:16px; background:#fff; padding:12px; border-radius:12px; overflow-x:auto; }
.tracking-table { width:100%; border-collapse:collapse; min-width:560px; }
.tracking-th { text-align:left; padding:10px 12px; font-size:13px; color:#374151; }
.tracking-row { border-top:1px solid #e5e7eb; }
.tracking-td { padding:12px; font-size:14px; color:#111827; }
.tracking-td-date { padding:12px; font-size:14px; color:#4b5563; }
.badge { display:inline-block; padding:6px 10px; border-radius:9999px; font-size:12px; font-weight:600; }
.badge-green { background:#dcfce7; color:#166534; }
.badge-yellow { background:#fef3c7; color:#92400e; }
.badge-red { background:#fee2e2; color:#991b1b; }
.badge-blue { background:#e0e7ff; color:#1e3a8a; }
.badge-gray { background:#e5e7eb; color:#374151; }
.tracking-empty { padding:16px; text-align:center; font-size:13px; color:#6b7280; }

.finance-panel{
    display: flex;
    background-color: white;
    flex-direction: column;
    height: 100%;
    max-height: 100%;
}
.emergency-panel{
    background:#fff;
    border-radius:16px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
    padding:16px;
    height: 100%;
    width: 100%;
}
.finance-title{
    font-size:16px;
    font-weight:700;
    color:#111827;
    text-align:center;
}
.finance-period{
    font-size:12px;
    color:#6b7280;
    text-align:center;
    margin-top:4px;
    margin-bottom:12px;
}
.finance-section-title{
    font-size:12px;
    font-weight:600;
    color:#111827;
    margin-bottom:8px;
    text-align:center;
}
.finance-donut-wrapper{
    display:flex;
    flex-direction:column;
    gap:16px;
}
.finance-donut{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:6px;
}
.finance-trend {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
.trend-chart {
    flex: 1;
    width: 100%;
}

.trend-legend{
    display:flex;
    gap:12px;
    align-items:center;
    font-size:12px;
    color:#4b5563;
    margin-bottom:6px;
    justify-content:center;
}
.legend-dot{
    width:10px;
    height:10px;
    border-radius:9999px;
    display:inline-block;
}
.donut-circle{
    width:100px;
    height:100px;
    border-radius:50%;
    background:#e5e7eb;
    display:flex;
    align-items:center;
    justify-content:center;
    background: conic-gradient(#b1b5e6 var(--percent, 0%), #e5e7eb 0);
}
.donut-inner{
    width:54px;
    height:54px;
    border-radius:50%;
    background:#fff;
}
.finance-amount{
    font-size:13px;
    font-weight:600;
    color:#111827;
}
.finance-saldo{
    margin-top:12px;
    padding:10px 12px;
    border-radius:12px;
    background:#f3f4ff;
    text-align:center;
}
.finance-saldo-label{
    font-size:11px;
    color:#4b5563;
    margin-bottom:4px;
}
.finance-saldo-value{
    font-size:16px;
    font-weight:700;
    color:#111827;
}
.emergency-title{
    margin-top: 12px;
    margin-bottom: 12px;
    font-size:16px;
    font-weight:700;
    color:#111827;
}
.emergency-period{
    font-size:12px;
    color:#6b7280;
    margin-top:4px;
    margin-bottom:12px;
}
.emergency-list{
    display:flex;
    gap:8px;
}
.emergency-item{
    border-radius:12px;
    border:1px solid #e5e7eb;
    padding:10px 12px;
    margin-top: 12px;
    flex:1 1 0;
    min-height:90px;
    display:flex;
    flex-direction:column;
}
.emergency-role{
    font-size:12px;
    font-weight:600;
    color:#4b5563;
}
.emergency-name{
    font-size:14px;
    font-weight:600;
    color:#111827;
    margin-top:2px;
}
.emergency-phone{
    font-size:18px;
    color:#16a34a;
    margin-top:4px;
    display:inline-flex;
    align-items:center;
}
.emergency-footer{
    margin-top:auto;
    display:flex;
    justify-content:flex-end;
}

.status-tabs { display:flex; gap:8px; background:#f3f4ff; padding:8px; border-radius:9999px; width:max-content; margin:0 auto 12px auto; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.tab { padding:6px 12px; border-radius:9999px; font-size:12px; font-weight:600; color:#1f2937; cursor:pointer; }
.tab-active { background:#636CCB; color:#fff; }
.section-title { font-size:18px; font-weight:700; color:#111827; margin-top:18px; margin-bottom:6px; display:flex; justify-content:space-between; align-items:center; }
.section-sub { font-size:12px; color:#6b7280; }
.progress { width:100%; background:#e5e7eb; border-radius:9999px; height:8px; }
.progress-inner-green { background:#16a34a; height:8px; border-radius:9999px; }
.progress-inner-red { background:#dc2626; height:8px; border-radius:9999px; }

.guide-panel{
    margin-top:16px;
    background:#fff;
    border-radius:16px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
    overflow:hidden;
}
.guide-header{
    padding:16px;
    border-bottom:1px solid #e5e7eb;
}
.guide-title{
    font-size:16px;
    font-weight:700;
    color:#111827;
}
.guide-subtitle{
    margin-top:4px;
    font-size:12px;
    color:#6b7280;
}
.guide-grid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:12px;
    padding:16px;
}
.guide-item{
    border-radius:14px;
    border:1px solid #e5e7eb;
    padding:14px;
    display:flex;
    flex-direction:column;
    gap:10px;
}
.guide-item-head{
    display:flex;
    align-items:center;
    gap:10px;
}
.guide-icon{
    width:36px;
    height:36px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f3f4ff;
    color:#636CCB;
    flex:0 0 auto;
}
.guide-item-title{
    font-size:14px;
    font-weight:700;
    color:#111827;
}
.guide-steps{
    display:flex;
    flex-direction:column;
    gap:10px;
}
.guide-step{
    display:flex;
    gap:10px;
    align-items:flex-start;
}
.guide-step-num{
    width:22px;
    height:22px;
    border-radius:9999px;
    background:#eef2ff;
    color:#3730a3;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:12px;
    font-weight:700;
    flex:0 0 auto;
}
.guide-step-text{
    font-size:12px;
    color:#374151;
    line-height:1.4;
}

@media (max-width: 1024px) {
  .dashboard-layout{
      flex-direction:column;
  }
  .overview {
      grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .guide-grid{
      grid-template-columns:1fr;
  }
}

@media (max-width: 640px) {
  .overview {
      grid-template-columns: 1fr;
      gap:10px;
  }
  .finance-donut-wrapper{
      flex-direction:row;
      overflow-x:auto;
      padding-bottom:6px;
  }
  .finance-donut{
      min-width:160px;
      flex:0 0 auto;
  }
  .title-overview h1 { font-size:22px; }
  .title-overview p { font-size:11px; }
  .btn-action { gap:6px; }
  .btn-action a { width:100%; justify-content:center; }
  .tracking-title { font-size:15px; }
  .tracking-wrapper { padding:10px; }
  .tracking-th { font-size:12px; }
  .tracking-td, .tracking-td-date { font-size:13px; }
}
</style>

<div class="title-overview">
    <h1>Hallo, {{$nama}}</h1>
    <h2>Selamat datang</h2>
</div>
@if($showActions ?? false)
    <div class="btn-action">
        <a href="/warga/pengaduans/create" class="btn-pengaduan"><i class="fa-solid fa-comments"></i> Ajukan Pengaduan</a>
        <a href="/warga/surat-ket-wargas/create" class="btn-surat"><i class="fa-solid fa-file-circle-plus"></i>Ajukan Surat</a>
        <a href="/warga/pembayaran-iurans" class="btn-iuran"><i class="fa-solid fa-money-bills"></i>Bayar Iuran</a>
    </div>
@else
    @if(($needsDataDiri ?? false))
        <div class="btn-action">
            <a href="{{ $dataDiriCreateUrl }}" class="btn-surat"><i class="fa-solid fa-id-card"></i> Lengkapi Data Diri</a>
        </div>
    @else
        @if(!empty($dataDiriViewUrl))
            <div class="btn-action">
                <a href="{{ $dataDiriViewUrl }}" class="btn-surat"><i class="fa-solid fa-id-card"></i> Lihat Data Diri</a>
            </div>
        @endif
    @endif
@endif
@if(($canShowDashboard ?? false))
<div class="dashboard-layout" x-data="{
    sync() {
        const l = this.$refs.left;
        const r = this.$refs.right;
        const c = this.$refs.center;
        const lh = l ? l.scrollHeight : 0;
        const rh = r ? r.scrollHeight : 0;
        const h = Math.max(lh, rh);
        if (c) {
            c.style.height = h + 'px';
            c.style.overflow = 'auto';
        }
    }
}" x-init="setTimeout(() => { sync(); window.addEventListener('resize', sync) }, 100)">
    <div class="dashboard-left" x-ref="left">
        <div style="font-size:16px; font-weight:600; margin-bottom:8px; color:#111827;">Ringkasan Layanan</div>
        <div class="overview">
          <div class="card">
            <div class="card-inner">
              <div class="label">Status iuran Bulan Ini</div>
              <div class="value {{ $iuranColor }}">{{ $iuranValue }}</div>
              <div class="desc">{{ $iuranDesc }}</div>
              <div class="icon">
                <i class="fa-solid fa-coins" style="font-size: 24px; color: #c4c8f2;"></i>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-inner">
              <div class="label">Pengaduan Terbaru</div>
              <div class="value">{{ $pengaduanStatus ?? 'Tidak ada' }}</div>
              @if($pengaduanTitle)
                <div class="desc">{{ $pengaduanTitle ?? '-' }}</div>
              @else
                <div class="desc">Belum ada pengaduan.</div>
              @endif
              <div class="icon">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 24px; color: #c4c8f2;"></i>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-inner">
              <div class="label">Surat Terbaru</div>
              <div class="value">{{ $suratValue ?? 'Tidak ada' }}</div>
              @if(($suratDesc ?? null) || ($suratTanggalPengajuan ?? null))
                @if($suratDesc)
                <div class="desc">{{ $suratDesc }}</div>
                @endif
                @if($suratTanggalPengajuan)
                <div class="desc">Diajukan {{ optional($suratTanggalPengajuan)->translatedFormat('d F Y') }}</div>
                @endif
              @else
                <div class="desc">Belum ada pengajuan surat.</div>
              @endif
              <div class="icon">
                <i class="fa-solid fa-file" style="font-size: 24px; color: #c4c8f2;"></i>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-inner">
              <div class="label">Kegiatan Terdekat</div>
              @php
                $kegiatanItemsLocal = is_array($kegiatanItems ?? null) ? $kegiatanItems : [];
                $kegiatanFirst = $kegiatanItemsLocal[0] ?? null;
              @endphp

              @if(!empty($kegiatanItemsLocal))
                <div class="desc_keg">{{ $kegiatanFirst['nama'] }} • {{ $kegiatanFirst['waktu'] ?? ($kegiatanDesc ?? '') }}</div>
                @foreach(array_slice($kegiatanItemsLocal, 1) as $item)
                  <div class="desc_keg">{{ $item['nama'] ?? '-' }} • {{ $item['waktu'] ?? '-' }}</div>
                @endforeach
              @elseif($kegiatanDesc)
                <div class="desc">{{ $kegiatanDesc }}</div>
              @else
                <div class="desc">Belum ada kegiatan terjadwal.</div>
              @endif
              <div class="icon">
                <i class="fa-solid fa-bullhorn" style="font-size: 24px; color: #c4c8f2;"></i>
              </div>
            </div>
          </div>
        </div>
    <div class="emergency-title">Hubungi Pengurus</div>
    <div class="dashboard-right" x-ref="right">
        <div class="emergency-panel">
            <div class="label">Berikut merupakan kontak untuk {{ $rtLabel ?? '' }}</div>
            <div class="emergency-list">
                @forelse(($emergencyContacts ?? []) as $contact)
                    <div class="emergency-item">
                        <div class="emergency-role">{{ $contact['jabatan'] ?? 'Kontak' }}</div>
                        <div class="emergency-name">{{ $contact['nama'] ?? '-' }}</div>
                        @if(!empty($contact['wa_url']))
                        <div class="icon">
                            <a href="{{ $contact['wa_url'] }}" target="_blank" class="emergency-phone">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="emergency-item">
                        <div class="emergency-role">Kontak belum tersedia</div>
                        <div class="emergency-name">Silakan hubungi pengurus RT</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    </div>
    <div class="dashboard-center" x-ref="center">
        <div class="finance-panel">
            <div class="finance-title">Info Keuangan RT</div>
            <form wire:submit.prevent="updateRekapPeriod" style="margin-top:6px; margin-bottom:8px; display:flex; justify-content:center; gap:6px; align-items:center;">
                <span style="font-size:12px; color:#6b7280;">Periode:</span>
                <select name="rekap_month" wire:model.defer="rekapSelectedMonth" style="border-radius:9999px; border:1px solid #e5e7eb; padding:4px 8px; font-size:12px;">
                    @foreach(($rekapMonthOptions ?? []) as $value => $label)
                        <option value="{{ $value }}" @selected((int)($rekapSelectedMonth ?? 0) === (int) $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="rekap_year" wire:model.defer="rekapSelectedYear" style="border-radius:9999px; border:1px solid #e5e7eb; padding:4px 8px; font-size:12px;">
                    @foreach(($rekapYearOptions ?? []) as $value)
                        <option value="{{ $value }}" @selected((int)($rekapSelectedYear ?? 0) === (int) $value)>{{ $value }}</option>
                    @endforeach
                </select>
                <button type="submit" style="border-radius:9999px; background:#636CCB; color:#fff; padding:4px 10px; font-size:12px;">
                    Lihat
                </button>
            </form>
            <div class="finance-period">{{ $rekapPeriodeLabel ?? '' }}</div>
            <div class="finance-donut-wrapper">
                <div class="finance-donut">
                    <div class="finance-section-title">Pemasukan</div>
                    <div class="donut-circle" style="--percent: {{ (int)($rekapPercent['masuk'] ?? 0) }}%;">
                        <div class="donut-inner"></div>
                    </div>
                    <div class="finance-amount">
                        Rp {{ number_format((int)($rekapData['total_masuk'] ?? 0), 0, ',', '.') }}
                    </div>
                </div>
                <div class="finance-donut">
                    <div class="finance-section-title">Pengeluaran</div>
                    <div class="donut-circle" style="--percent: {{ (int)($rekapPercent['keluar'] ?? 0) }}%;">
                        <div class="donut-inner"></div>
                    </div>
                    <div class="finance-amount">
                        Rp {{ number_format((int)($rekapData['total_keluar'] ?? 0), 0, ',', '.') }}
                    </div>
                </div>
            </div>
            <div class="finance-saldo">
                <div class="finance-saldo-label">SALDO AKHIR</div>
                <div class="finance-saldo-value">
                    Rp {{ number_format((int)($rekapData['saldo_akhir'] ?? 0), 0, ',', '.') }}
                </div>
            </div>

        </div>
    </div>
</div>
<div class="guide-panel">
    <div class="guide-header">
        <div class="guide-title">Panduan Penggunaan Layanan</div>
        <div class="guide-subtitle">Ikuti langkah-langkah mudah berikut untuk mendapatkan layanan terbaik.</div>
    </div>
    <div class="guide-grid">
        <div class="guide-item">
            <div class="guide-item-head">
                <div class="guide-icon"><i class="fa-solid fa-comments"></i></div>
                <div class="guide-item-title">Layanan Pengaduan</div>
            </div>
            <div class="guide-steps">
                <div class="guide-step">
                    <div class="guide-step-num">1</div>
                    <div class="guide-step-text">Klik "Ajukan Pengaduan" lalu isi formulir dengan detail masalah.</div>
                </div>
                <div class="guide-step">
                    <div class="guide-step-num">2</div>
                    <div class="guide-step-text">Unggah foto bukti jika ada, kemudian kirim laporan.</div>
                </div>
                <div class="guide-step">
                    <div class="guide-step-num">3</div>
                    <div class="guide-step-text">Pantau status pengaduan pada bagian Tracking Layanan.</div>
                </div>
            </div>
        </div>
        <div class="guide-item">
            <div class="guide-item-head">
                <div class="guide-icon"><i class="fa-solid fa-file-circle-plus"></i></div>
                <div class="guide-item-title">Pengajuan Surat</div>
            </div>
            <div class="guide-steps">
                <div class="guide-step">
                    <div class="guide-step-num">1</div>
                    <div class="guide-step-text">Klik "Ajukan Surat" dan pilih jenis surat yang dibutuhkan.</div>
                </div>
                <div class="guide-step">
                    <div class="guide-step-num">2</div>
                    <div class="guide-step-text">Lengkapi keperluan serta unggah dokumen pendukung.</div>
                </div>
                <div class="guide-step">
                    <div class="guide-step-num">3</div>
                    <div class="guide-step-text">Tunggu verifikasi, lalu cek status pada Tracking Layanan.</div>
                </div>
            </div>
        </div>
        <div class="guide-item">
            <div class="guide-item-head">
                <div class="guide-icon"><i class="fa-solid fa-money-bills"></i></div>
                <div class="guide-item-title">Pembayaran Iuran</div>
            </div>
            <div class="guide-steps">
                <div class="guide-step">
                    <div class="guide-step-num">1</div>
                    <div class="guide-step-text">Klik "Bayar Iuran" untuk melihat tagihan yang tersedia.</div>
                </div>
                <div class="guide-step">
                    <div class="guide-step-num">2</div>
                    <div class="guide-step-text">Pilih metode pembayaran yang tersedia lalu selesaikan pembayaran.</div>
                </div>
                <div class="guide-step">
                    <div class="guide-step-num">3</div>
                    <div class="guide-step-text">Status pembayaran akan otomatis ter-update di dashboard.</div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="tracking-title">Tracking Layanan Saya</div>
<div x-data="{active: '{{ $trackingActiveFitur }}'}">
    <div class="status-tabs">
        @foreach(($trackingFiturLabels ?? []) as $lbl)
            <div class="tab" :class="{ 'tab-active': active === '{{ $lbl }}' }" @click="active='{{ $lbl }}'">{{ $lbl }}</div>
        @endforeach
    </div>
<div class="tracking-wrapper">
    <table class="tracking-table">
        <thead>
            <tr>
                <th class="tracking-th">Jenis Layanan</th>
                <th class="tracking-th">Tanggal Pengajuan</th>
                <th class="tracking-th">Status</th>
            </tr>
        </thead>

        <tbody>
        @forelse(($trackingFiturLabels ?? []) as $lbl)
            @php($rows = ($trackingByFitur[$lbl] ?? []))

            @forelse($rows as $row)
                <tr class="tracking-row" x-show="active === @js($lbl)">
                    <td class="tracking-td">{{ $row['jenis'] ?? '-' }}</td>
                    <td class="tracking-td-date">
                        {{ optional($row['tanggal'] ?? null)->translatedFormat('d F Y') ?? '-' }}
                    </td>
                    <td class="tracking-td">
                        @php($badge = $row['badge'] ?? 'gray')
                        <span class="badge {{ 'badge-' . $badge }}">
                            {{ $row['label'] ?? '-' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr x-show="active === @js($lbl)">
                    <td colspan="3" class="tracking-empty">Tidak ada data untuk {{ $lbl }}.</td>
                </tr>
            @endforelse

        @empty
            <tr>
                <td colspan="3" class="tracking-empty">Belum ada data layanan.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

</div>
@endif
</x-filament::widget>
