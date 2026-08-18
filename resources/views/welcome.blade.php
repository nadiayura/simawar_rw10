@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
if (! function_exists('format_period')) {
    function format_period($start, $end)
    {
        try {
            $s = $start ? \Carbon\Carbon::parse($start) : null;
            $e = $end ? \Carbon\Carbon::parse($end) : null;
        } catch (\Throwable $th) {
            $s = null;
            $e = null;
        }
        if ($s && $e) {
            return $s->format('M Y') . ' – ' . $e->format('M Y');
        }
        if ($s) {
            return $s->format('M Y') . ' – ';
        }
        if ($e) {
            return ' – ' . $e->format('M Y');
        }
        return '-';
    }
}
if (! function_exists('normalize_phone')) {
    function normalize_phone($raw)
    {
        $digits = preg_replace('/\D+/', '', (string) ($raw ?? ''));
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }
        return $digits;
    }
}

@endphp
<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAWAR 10 - Sistem Manajemen Warga RW 10</title>
  <link rel="icon" type="image/png" href="{{ Storage::url('logo/logoutama.png') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  @vite('resources/css/app.css')
  @vite('resources/css/landingpage.css')
</head>

<body class="font-sans antialiased text-gray-800">
  <style>
    .rw-card {
      width: 100%;
      max-width: 800px;
      margin: 0 auto;
      border-radius: 16px;
      background: #f9fafb;
      display: flex;
      gap: 8px;
      padding: 0.75rem;
    }

    .rw-panel {
      height: 220px;
      flex: 1;
      overflow: hidden;
      cursor: pointer;
      border-radius: 14px;
      transition: all .5s;
      display: flex;
      justify-content: center;
      align-items: stretch;
      border-width: 1px;
      border-style: solid;
      background-color: #eff6ff;
    }

    .rw-panel:hover {
      flex: 4;
    }

    .rw-panel-blue {
      border-color: #2563eb;
      background-color: #eff6ff;
    }

    .rw-panel-emerald {
      border-color: #10b981;
      background-color: #ecfdf5;
    }

    .rw-panel-amber {
      border-color: #f59e0b;
      background-color: #fffbeb;
    }

    .rw-panel-inner {
      position: relative;
      width: 100%;
      height: 100%;
      padding: 1rem 0.75rem;
    }

    .rw-role-initial,
    .rw-content {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
      text-align: center;
      color: #111827;
      transition: opacity .3s, transform .3s;
    }

    .rw-role-initial {
      font-size: 0.8rem;
      letter-spacing: .08em;
      text-transform: uppercase;
      font-weight: 700;
      transform: translateY(0);
      opacity: 1;
    }

    .rw-content {
      opacity: 0;
      transform: translateY(10px);
    }

    .rw-panel:hover .rw-role-initial {
      opacity: 0;
      transform: translateY(-10px);
    }

    .rw-panel:hover .rw-content {
      opacity: 1;
      transform: translateY(0);
    }

    .rt-card .rw-role-initial {
      transform: translateY(0) rotate(-90deg);
    }

    .rt-card .rw-panel:hover .rw-role-initial {
      transform: translateY(-10px) rotate(-90deg);
    }

    .rw-photo {
      width: 64px;
      height: 64px;
      border-radius: 9999px;
      overflow: hidden;
      background-color: #e5e7eb;
      flex-shrink: 0;
    }

    .rw-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .rw-text {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .rw-role {
      font-size: 0.7rem;
      letter-spacing: .08em;
      text-transform: uppercase;
      font-weight: 700;
      color: #1d4ed8;
    }

    .rw-name {
      font-size: 0.9rem;
      font-weight: 600;
      color: #111827;
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
    }

    .rw-phone {
      font-size: 0.75rem;
      font-weight: 500;
      color: #4b5563;
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
    }

    @media (max-width: 768px) {
      .rw-card {
        flex-direction: column;
        height: auto;
      }

      .rw-panel {
        height: 150px;
      }
    }
  </style>
  @include('partials.navbar')
  <!-- Hero Section -->
  <section class="relative bg-gradient-to-b from-[#596fb4] to-[#e9ebf3] text-white py-20">
    <!-- <div class="absolute inset-0" style="background-image: url('{{ asset('storage/img/bg.jpg') }}'); background-size: cover; background-position: center; opacity: 0.2;"></div> -->
    <div class="container mx-auto px-4 relative z-10">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Selamat Datang di<br>Layanan Warga RW 010</h1>
        <p class="text-lg mb-8">Sistem informasi tersedia untuk memudahkan layanan administrasi, pengaduan, surat keterangan, serta kegiatan warga secara online dan terukur.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <a href="http://127.0.0.1:8000/warga/login" class="bg-[#596fb4] hover:bg-[#4a5990] text-white font-medium px-6 py-3 rounded-md transition">Buat Permohonan</a>
          <a href="http://127.0.0.1:8000/warga/login" class="bg-white hover:bg-gray-100 text-blue-800 font-medium px-6 py-3 rounded-md transition">Lapor Pengaduan</a>
        </div>
        <div class="mt-10 grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 text-gray-900">
          <div class="rounded-2xl px-5 py-4 flex flex-col gap-1">
            <div class="text-xxl md:text-2xl font-bold text-[#133396]">
              {{ number_format($rtCount ?? 0) }} RT
            </div>
            <div class="text-xs font-semibold text-gray-500 mt-2">Wilayah Cakupan</div>
          </div>
          <div class="rounded-2xl px-5 py-4 flex flex-col gap-1">
            <div class="text-xl md:text-2xl font-bold text-[#133396]">
              {{ number_format($wargaCount ?? 0) }}
            </div>
            <div class="text-xs font-semibold text-gray-500 mt-2">KK Terdaftar</div>
          </div>
          <div class=" rounded-2xl px-5 py-4 flex flex-col gap-1">
            <div class="text-xl md:text-2xl font-bold text-[#133396]">24/7</div>
            <div class="text-xs font-semibold text-gray-500 mt-2">Layanan Online</div>
          </div>
          <div class=" rounded-2xl px-5 py-4 flex flex-col gap-1">
            <div class="text-xl md:text-2xl font-bold text-[#133396]">
            @php
             $totalSelesai = ($pengaduanSelesaiCount ?? 0) + ($suratSelesaiCount ?? 0);
            @endphp
                {{ number_format($totalSelesai) }}
            </div>
            <div class="relative inline-block group mt-1">
              <button type="button" class="relative px-0 py-0 font-semibold text-xs text-gray-500 focus:outline-none transition-colors">
                <span class="flex items-center gap-1">
                  <!-- <svg viewBox="0 0 24 24" stroke="currentColor" fill="none" class="w-3.5 h-3.5">
                    <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"></path>
                  </svg> -->
                  <span>Layanan Ditangani</span>
                </span>
              </button>
              <div class="absolute invisible opacity-0 group-hover:visible group-hover:opacity-100 bottom-full left-1/2 -translate-x-1/2 mb-3 w-72 transition-all duration-300 ease-out transform group-hover:translate-y-0 translate-y-2">
                <div class="relative p-4 bg-gradient-to-br from-gray-900/95 to-gray-800/95 backdrop-blur-md rounded-2xl border border-white/10 shadow-[0_0_30px_rgba(79,70,229,0.15)]">
                  <div class="flex items-center gap-3 mb-2">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-500/20" >
                      <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-indigo-400" >
                        <path clip-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" fill-rule="evenodd" ></path>
                      </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Ringkasan Layanan</h3>
                  </div>
                  <div class="space-y-2">
                    <p class="text-sm text-gray-300">
                      Pengaduan berhasil diatasi:
                      <span class="font-semibold text-white">
                        {{ number_format($pengaduanSelesaiCount ?? 0) }}
                      </span>
                    </p>
                    <p class="text-sm text-gray-300">
                      Surat keterangan tertangani:
                      <span class="font-semibold text-white">
                        {{ number_format($suratSelesaiCount ?? 0) }}
                      </span>
                    </p>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                      <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                        <path
                          clip-rule="evenodd"
                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                          fill-rule="evenodd"
                        ></path>
                      </svg>
                      <span>Data Sudah Terupdate</span>
                    </div>
                  </div>
                  <div
                    class="absolute inset-0 rounded-2xl bg-gradient-to-r from-indigo-500/10 to-purple-500/10 blur-xl opacity-50"
                  ></div>
                  <div
                    class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3 h-3 bg-gradient-to-br from-gray-900/95 to-gray-800/95 rotate-45 border-r border-b border-white/10"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="py-16 bg-gradient-to-b from-[#e9ebf3] to-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-10">
        <h2 class="text-3xl font-bold mb-3">Aktivitas & Informasi Warga</h2>
        <p class="text-gray-600 max-w-2xl mx-auto text-sm md:text-base">
          Kegiatan dan informasi terkini dari lingkungan RW 010, mulai dari berita kesehatan sampai kegiatan Karang Taruna.
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-100 shadow-sm p-6 flex flex-col md:flex-row gap-5">
          <div class="flex-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold mb-3">
              <span>Kegiatan Kesehatan</span>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Berita Kesehatan Warga</h3>
            <p class="text-sm text-gray-600 mb-4">
              Ikuti informasi kesehatan terkini seperti Posyandu dan Posbindu yang rutin dilaksanakan di lingkungan RW 010.
            </p>
            <a href="{{ route('kegiatan-kesehatan') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#4c67b8] text-white text-sm font-semibold hover:bg-[#2E4EAE] transition-colors">
              <span>Lihat Berita Kesehatan</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </a>
          </div>
          @php
            $beritaPreview = $beritaKesehatan->first();
          @endphp
          <div class="md:w-40 h-32 rounded-xl overflow-hidden bg-blue-200/40 flex items-center justify-center">
            @if($beritaPreview && $beritaPreview->dokumentasi && is_array($beritaPreview->dokumentasi) && count($beritaPreview->dokumentasi) > 0)
              <img src="{{ Storage::url($beritaPreview->dokumentasi[0]) }}" alt="{{ $beritaPreview->nama_kegiatan }}" class="w-full h-full object-cover">
            @else
              <div class="text-center text-blue-800 text-xs px-3">
                Tidak ada foto, namun kegiatan tetap berjalan aktif.
              </div>
            @endif
          </div>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 shadow-sm p-6 flex flex-col md:flex-row gap-5">
          <div class="flex-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold mb-3">
              <span>Kegiatan RT & Karang Taruna</span>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Kegiatan Lingkungan & Sosial</h3>
            <p class="text-sm text-gray-600 mb-4">
              Dokumentasi kerja bakti, kegiatan pemuda, dan program sosial lain yang mempererat warga satu sama lain.
            </p>
            <a href="{{ route('galeri') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-500 text-white text-sm font-semibold hover:bg-emerald-600 transition-colors">
              <span>Lihat Kegiatan RT</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </a>
          </div>
          @php
            $katarPreview = $galeriKatar->first();
          @endphp
          <div class="md:w-40 h-32 rounded-xl overflow-hidden bg-emerald-200/40 flex items-center justify-center">
            @if($katarPreview && $katarPreview->foto_kegiatan)
              <img src="{{ Storage::url($katarPreview->foto_kegiatan) }}" alt="{{ $katarPreview->nama_kegiatan }}" class="w-full h-full object-cover">
            @else
              <div class="text-center text-emerald-800 text-xs px-3">
                Kegiatan siap dilaksanakan, dokumentasi akan segera menyusul.
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>
  <section id="struktural" class="py-16 bg-gradient-to-b from-white to-emerald-50">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-center mb-3 text-black">Bagan Struktur RT & RW</h2>
      <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">Mengenal lebih dekat para pengurus RT dan RW yang berkomitmen melayani warga.</p>

      <!-- RW -->
      <div class="max-w-5xl mx-auto bg-white border border-blue-100 rounded-2xl shadow-lg p-6 md:p-8 mb-10">
        <h3 class="text-lg font-semibold text-[#133396] mb-4">Struktur RW 010</h3>
        <div class="space-y-4">
          <div class="rounded-xl bg-blue-50 border border-blue-200 p-4">
            <div class="text-sm font-semibold text-blue-800 mb-1">Ketua RW</div>
            <div class="text-base font-medium text-gray-900">
              {{ $rwStructure['ketua']?->warga?->nama ?? $rwStructure['ketua']->nama ?? 'Belum ada data' }}
            </div>
            @php
              $hpKetuaRw = $rwStructure['ketua']?->warga?->no_hp ?? $rwStructure['ketua']?->no_hp ?? null;
              $waKetuaRw = $hpKetuaRw ? normalize_phone($hpKetuaRw) : null;
            @endphp
            <div class="text-xs text-gray-600">Periode: {{ format_period($rwStructure['ketua']?->periode_mulai ?? $rwStructure['ketua']?->warga?->periode_mulai ?? null, $rwStructure['ketua']?->periode_selesai ?? $rwStructure['ketua']?->warga?->periode_selesai ?? null) }}</div>
            <div class="text-sm text-emerald-700 flex justify-end mt-auto">
              @if($waKetuaRw)
                <a href="https://wa.me/{{ $waKetuaRw }}" target="_blank" class="emergency-phone">
                     <i class="fa-brands fa-whatsapp  text-green-600 text-lg"></i>
                     <span class="sr-only">WhatsApp</span>
                </a>
              @else
                -
              @endif
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4">
              <div class="text-sm font-semibold text-emerald-800 mb-1">Sekretaris RW</div>
              <div class="text-base font-medium text-gray-900">
                {{ $rwStructure['sekretaris']?->warga?->nama ?? $rwStructure['sekretaris']->nama ?? 'Belum ada data' }}
              </div>
            @php
              $hpSekRw = $rwStructure['sekretaris']?->warga?->no_hp ?? $rwStructure['sekretaris']?->no_hp ?? null;
              $waSekRw = $hpSekRw ? normalize_phone($hpSekRw) : null;
            @endphp
             <div class="text-xs text-gray-600">Periode: {{ format_period($rwStructure['sekretaris']?->periode_mulai ?? $rwStructure['sekretaris']?->warga?->periode_mulai ?? null, $rwStructure['sekretaris']?->periode_selesai ?? $rwStructure['sekretaris']?->warga?->periode_selesai ?? null) }}</div>
              <div class="text-sm text-emerald-700 flex justify-end mt-auto">
                @if($waSekRw)
                 <a href="https://wa.me/{{ $waSekRw }}" target="_blank" class="emergency-phone">
                     <i class="fa-brands fa-whatsapp  text-green-600 text-lg"></i>
                     <span class="sr-only">WhatsApp</span>
                </a>

                @else
                  -
                @endif
              </div>
            </div>
            <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">
              <div class="text-sm font-semibold text-amber-800 mb-1">Bendahara RW</div>
              <div class="text-base font-medium text-gray-900">
                {{ $rwStructure['bendahara']?->warga?->nama ?? $rwStructure['bendahara']->nama ?? 'Belum ada data' }}
              </div>
            @php
              $hpBenRw = $rwStructure['bendahara']?->warga?->no_hp ?? $rwStructure['bendahara']?->no_hp ?? null;
              $waBenRw = $hpBenRw ? normalize_phone($hpBenRw) : null;
            @endphp
            <div class="text-xs text-gray-600">Periode: {{ format_period($rwStructure['bendahara']?->periode_mulai ?? $rwStructure['bendahara']?->warga?->periode_mulai ?? null, $rwStructure['bendahara']?->periode_selesai ?? $rwStructure['bendahara']?->warga?->periode_selesai ?? null) }}</div>
              <div class="text-sm text-emerald-700 flex justify-end mt-auto">
                @if($waBenRw)
                   <a href="https://wa.me/{{ $waBenRw }}" target="_blank" class="emergency-phone">
                     <i class="fa-brands fa-whatsapp  text-green-600 text-lg"></i>
                     <span class="sr-only">WhatsApp</span>
                </a>
                @else
                  -
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RT -->
      @if(count($rtStructures) > 0)
      <div class="max-w-6xl mx-auto bg-white border border-purple-100 rounded-2xl shadow-lg p-6 md:p-8">
        <h3 class="text-lg font-semibold text-[#133396] mb-4">Struktur RT</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          @foreach($rtStructures as $rtNumber => $rtStructure)
          <div class="rounded-2xl border border-gray-100 bg-gray-50/50 px-4 py-3">
            <div class="flex items-center justify-between mb-3">
              <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">RT</div>
              <div class="text-sm font-bold text-[#133396]">RT {{ str_pad($rtNumber, 3, '0', STR_PAD_LEFT) }}</div>
            </div>

            <div class="space-y-4">
              <div class="rounded-xl bg-blue-50 border border-blue-200 p-4 flex flex-col min-h-[140px]">
                <div class="text-sm font-semibold text-blue-800 mb-1">Ketua RT</div>
                <div class="text-base font-medium text-gray-900">
                  {{ $rtStructure['ketua']->nama ?? $rtStructure['ketua']?->warga?->nama ?? 'Belum ada data' }}
                </div>
                @php
                  $hpKetuaRt = $rtStructure['ketua']?->warga?->no_hp ?? $rtStructure['ketua']?->no_hp ?? null;
                  $waKetuaRt = $hpKetuaRt ? normalize_phone($hpKetuaRt) : null;
                @endphp
                <div class="mt-auto text-xs text-gray-600">Periode: {{ format_period($rtStructure['ketua']?->periode_mulai ?? $rtStructure['ketua']?->warga?->periode_mulai ?? null, $rtStructure['ketua']?->periode_selesai ?? $rtStructure['ketua']?->warga?->periode_selesai ?? null) }}</div>
                <div class="text-sm text-emerald-700 flex justify-end mt-auto">
                  @if($waKetuaRt)
                    <a href="https://wa.me/{{ $waKetuaRt }}" target="_blank" class="emergency-phone">
                     <i class="fa-brands fa-whatsapp  text-green-600 text-lg"></i>
                     <span class="sr-only">WhatsApp</span>
                </a>
                  @else
                    -
                  @endif
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex flex-col h-[160px]">
                  <div class="text-sm font-semibold text-emerald-800 mb-1">Sekretaris RT</div>
                  <div class="text-base font-medium text-gray-900">
                    {{ $rtStructure['sekretaris']->nama ?? $rtStructure['sekretaris']?->warga?->nama ?? 'Belum ada data' }}
                  </div>
                  @php
                    $hpSekRt = $rtStructure['sekretaris']?->warga?->no_hp ?? $rtStructure['sekretaris']?->no_hp ?? null;
                    $waSekRt = $hpSekRt ? normalize_phone($hpSekRt) : null;
                  @endphp
                  <div class="text-sm text-emerald-700 flex justify-end mt-auto">
                  <div class="mt-auto text-xs text-gray-600">Periode: {{ format_period($rtStructure['sekretaris']?->periode_mulai ?? $rtStructure['sekretaris']?->warga?->periode_mulai ?? null, $rtStructure['sekretaris']?->periode_selesai ?? $rtStructure['sekretaris']?->warga?->periode_selesai ?? null) }}</div>
                    @if($waSekRt)
                    <a href="https://wa.me/{{ $waSekRt }}" target="_blank" class="emergency-phone">
                        <i class="fa-brands fa-whatsapp  text-green-600 text-lg"></i>
                        <span class="sr-only">WhatsApp</span>
                    </a>
                    @else
                      -
                    @endif
                  </div>
                </div>
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 flex flex-col h-[160px]">
                  <div class="text-sm font-semibold text-amber-800 mb-1">Bendahara RT</div>
                  <div class="text-base font-medium text-gray-900">
                    {{ $rtStructure['bendahara']->nama ?? $rtStructure['bendahara']?->warga?->nama ?? 'Belum ada data' }}
                  </div>
                  @php
                    $hpBenRt = $rtStructure['bendahara']?->warga?->no_hp ?? $rtStructure['bendahara']?->no_hp ?? null;
                    $waBenRt = $hpBenRt ? normalize_phone($hpBenRt) : null;
                  @endphp
                  <div class="text-sm text-emerald-700 flex justify-end mt-auto">
                    <div class="mt-auto text-xs text-gray-600">Periode: {{ format_period($rtStructure['bendahara']?->periode_mulai ?? $rtStructure['bendahara']?->warga?->periode_mulai ?? null, $rtStructure['bendahara']?->periode_selesai ?? $rtStructure['bendahara']?->warga?->periode_selesai ?? null) }}</div>
                    @if($waBenRt)
                    <a href="https://wa.me/{{ $waBenRt }}" target="_blank" class="emergency-phone">
                        <i class="fa-brands fa-whatsapp  text-green-600 text-lg"></i>
                        <span class="sr-only">WhatsApp</span>
                      </a>
                    @else
                      -
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endif
    </div>
  </section>

  <!-- Cara Menggunakan Section -->
  <section class="py-16 bg-gradient-to-b from-emerald-50 to-green-50">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-center mb-3">Cara Menggunakan Sistem</h2>
      <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">Ikuti langkah-langkah berikut untuk menggunakan layanan digital SIMAWAR dengan mudah.</p>

      <div class="relative">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
          <!-- Step 1 -->
          <div class="relative">
            <!-- Horizontal line to the right of step 1 -->
            <div class="hidden lg:block absolute top-6 left-[calc(50%+18px)] w-[calc(100%-36px)] h-1 bg-[#1E3A8A]"></div>
            <div class="w-12 h-12 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center text-xl font-bold mb-4 mx-auto z-20 relative">1</div>
            <h3 class="text-lg font-semibold text-center mb-2">Daftar Akun</h3>
            <p class="text-gray-600 text-center">Buat akun dengan mengisi formulir pendaftaran dan verifikasi data diri Anda.</p>
          </div>

          <!-- Step 2 -->
          <div class="relative">
            <!-- Horizontal line to the right of step 2 -->
            <div class="hidden lg:block absolute top-6 left-[calc(50%+18px)] w-[calc(100%-36px)] h-1 bg-[#1E3A8A]"></div>
            <!-- Horizontal line to the left of step 2 -->
            <div class="hidden lg:block absolute top-6 right-[calc(50%+18px)] w-[calc(100%-36px)] h-1 bg-[#1E3A8A]"></div>
            <div class="w-12 h-12 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center text-xl font-bold mb-4 mx-auto z-20 relative">2</div>
            <h3 class="text-lg font-semibold text-center mb-2">Menunggu Verifikasi</h3>
            <p class="text-gray-600 text-center">Akun ditinjau admin, setelah terverifikasi akun langsung dapat digunakan.</p>
          </div>

          <!-- Step 3 -->
          <div class="relative">
            <!-- Horizontal line to the right of step 2 -->
            <div class="hidden lg:block absolute top-6 left-[calc(50%+18px)] w-[calc(100%-36px)] h-1 bg-[#1E3A8A]"></div>
            <!-- Horizontal line to the left of step 2 -->
            <div class="hidden lg:block absolute top-6 right-[calc(50%+18px)] w-[calc(100%-36px)] h-1 bg-[#1E3A8A]"></div>
            <div class="w-12 h-12 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center text-xl font-bold mb-4 mx-auto z-20 relative">3</div>
            <h3 class="text-lg font-semibold text-center mb-2">Pilih Layanan</h3>
            <p class="text-gray-600 text-center">Pilih layanan yang Anda butuhkan dari berbagai fitur yang tersedia di dashboard.</p>
          </div>

          <!-- Step 4 -->
          <div class="relative">
            <!-- Horizontal line to the right of step 3 -->
            <div class="hidden lg:block absolute top-6 left-[calc(50%+18px)] w-[calc(100%-36px)] h-1 bg-[#1E3A8A]"></div>
            <!-- Horizontal line to the left of step 3 -->
            <div class="hidden lg:block absolute top-6 right-[calc(50%+18px)] w-[calc(100%-36px)] h-1 bg-[#1E3A8A]"></div>
            <div class="w-12 h-12 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center text-xl font-bold mb-4 mx-auto z-20 relative">4</div>
            <h3 class="text-lg font-semibold text-center mb-2">Isi Formulir</h3>
            <p class="text-gray-600 text-center">Lengkapi formulir pengajuan dengan data yang benar dan lengkap sesuai kebutuhan.</p>
          </div>

          <!-- Step 5 -->
          <div class="relative">
            <!-- Horizontal line to the left of step 4 -->
            <div class="hidden lg:block absolute top-6 right-[calc(50%+18px)] w-[calc(100%-36px)] h-1 bg-[#1E3A8A]"></div>
            <div class="w-12 h-12 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center text-xl font-bold mb-4 mx-auto z-20 relative">5</div>
            <h3 class="text-lg font-semibold text-center mb-2">Proses & Notifikasi</h3>
            <p class="text-gray-600 text-center">Pengajuan Anda akan diproses dan Anda akan mendapatkan notifikasi melalui WhatsApp.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  @include('partials.footer')
  <script>
    function filterKegiatan(filter) {
      const cards = document.querySelectorAll('.kegiatan-card');
      const filterButtons = document.querySelectorAll('.filter-btn');

      filterButtons.forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700');
        if (btn.dataset.filter === 'posyandu') {
          btn.classList.add('bg-pink-200', 'text-pink-800', 'hover:bg-pink-200');
        } else if (btn.dataset.filter === 'posbindu') {
          btn.classList.add('bg-green-200', 'text-green-800', 'hover:bg-green-200');
        } else {
          btn.classList.add('bg-gray-200', 'text-gray-800', 'hover:bg-gray-200');
        }
      });

      cards.forEach(card => {
        if (filter === 'all') {
          card.style.display = 'block';
        } else {
          const jenisKegiatan = card.dataset.jenis;
          if (jenisKegiatan && jenisKegiatan.includes(filter)) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        }
      });
    }
  </script>
</body>

</html>
