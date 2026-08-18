@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Berita Kegiatan Kesehatan - SIMAWAR RW 10</title>
  <link rel="icon" type="image/png" href="{{ \Illuminate\Support\Facades\Storage::url('logo/logoutama.png') }}">
  @vite('resources/css/app.css')
  @vite('resources/css/landingpage.css')
</head>

<body class="font-sans antialiased text-gray-800 bg-gray-50">
  @include('partials.navbar')

  <!-- Page Title Section -->
 <section class="bg-gradient-to-b from-[#4C67B8] to-white text-gray-800 py-16">
  <div class="container mx-auto px-4">
    <!-- Tombol Kembali di kiri -->
    <div class="flex items-center justify-start mb-4">
      <a href="{{ route('welcome') }}" class="flex items-center space-x-2 text-white hover:text-blue-200 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        <span class="font-medium flex items-center gap-1">Kembali</span>
      </a>
    </div>


    <div class="text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Kegiatan Karang Taruna</h1>
        <p class="text-xl max-w-2xl mx-auto">Dokumentasi lengkap berbagai kegiatan dan acara yang telah dilaksanakan di lingkungan RW 010 Tanah Baru, Beji.</p>
      </div>
  </div>
</section>

  <!-- Main Content -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Filter Section -->
    <div class="mb-8">
      <div class="flex flex-wrap gap-3 justify-center">
        <a href="{{ route('kegiatan-kesehatan') }}"
           class="px-6 py-2 rounded-full text-sm font-medium  {{ !request('filter') || request('filter') == 'all' ? 'bg-blue-300 text-blue-700' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
          Semua
        </a>
        <a href="{{ route('kegiatan-kesehatan', ['filter' => 'posyandu']) }}"
           class="px-6 py-2 rounded-full text-sm font-medium  {{ request('filter') == 'posyandu' ? 'bg-pink-100 text-pink-800' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
          POSYANDU
        </a>
        <a href="{{ route('kegiatan-kesehatan', ['filter' => 'posbindu']) }}"
           class="px-6 py-2 rounded-full text-sm font-medium {{ request('filter') == 'posbindu' ? 'bg-green-100 text-green-800' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
          POSBINDU
        </a>
      </div>
    </div>


    @if($kegiatanKesehatan->count() > 0)
      @foreach($groupedKegiatanKesehatan as $monthKey => $items)
      <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">
        {{ $monthLabelsKegiatan[$monthKey] ?? $monthKey }}
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @foreach($items as $kegiatan)
        @php
          $statusNameEarly = strtolower($kegiatan->status?->keterangan ?? '');
        @endphp
        @if(in_array($statusNameEarly, ['dijadwalkan','terjadwal'], true))
          @continue
        @endif
        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 border border-gray-200">
        <!-- Header dengan tanggal dan status -->
        <div class="p-4 bg-white border-b border-gray-200">
          <div class="flex justify-between items-start mb-2">
            <div class="text-sm text-gray-600">
              {{ \Carbon\Carbon::parse($kegiatan->tgl)->format('d M Y') }}
            </div>
            <div class="flex items-center space-x-2">
              @php
                $statusName = strtolower($kegiatan->status?->keterangan ?? '');
                $statusClass = match($statusName) {
                  'selesai' => 'bg-green-500 text-white',
                  'dibatalkan' => 'bg-red-500 text-white',
                  default => 'bg-blue-500 text-white'
                };
                $statusText = $kegiatan->status?->keterangan ?? 'Status';
              @endphp
              <span class="px-3 py-1 rounded-lg text-xs font-medium {{ $statusClass }}">
                {{ $statusText }}
              </span>
            </div>
          </div>

          @php
            $jenisClass = 'bg-blue-100 text-blue-800';
            if (stripos($kegiatan->jenis_kegiatan, 'posyandu') !== false) {
              $jenisClass = 'bg-pink-100 text-pink-800';
            } elseif (stripos($kegiatan->jenis_kegiatan, 'posbindu') !== false) {
              $jenisClass = 'bg-green-100 text-green-800';
            }
          @endphp
          <span class="inline-block px-3 py-1 rounded-full text-xs font-medium {{ $jenisClass }}">
            {{ $kegiatan->jenis_kegiatan }}
          </span>
        </div>

        <!-- Image/Placeholder -->
        <div class="h-48 bg-gray-100 flex items-center justify-center">
          @if($kegiatan->dokumentasi && is_array($kegiatan->dokumentasi) && count($kegiatan->dokumentasi) > 0)
            <img src="{{ Storage::url($kegiatan->dokumentasi[0]) }}"
                 alt="{{ $kegiatan->judul }}"
                 class="w-full h-full object-cover">
          @else
            <!-- Diagram placeholder -->
            <div class="text-center p-4">
              <div class="w-24 h-24 mx-auto mb-3 bg-gray-200 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-2xl text-gray-400"></i>
              </div>
              <div class="space-y-1">
                <div class="h-2 bg-gray-200 rounded w-16 mx-auto"></div>
                <div class="h-2 bg-gray-200 rounded w-12 mx-auto"></div>
                <div class="h-2 bg-gray-200 rounded w-20 mx-auto"></div>
              </div>
            </div>
          @endif
        </div>

        <!-- Content -->
        <div class="p-4">
          <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">
            {{ $kegiatan->nama_kegiatan }}
          </h3>

          <div class="flex flex-wrap gap-2 mb-3">
            @php
              $aktivitas = strtolower($kegiatan->aktivitas_dilakukan ?? '');
            @endphp
            @if(str_contains($aktivitas, 'imunisasi') || str_contains($aktivitas, 'vaksin'))
              <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">Imunisasi</span>
            @endif
            @if(str_contains($aktivitas, 'vitamin') || str_contains($aktivitas, 'suplemen'))
              <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Pemberian Vitamin</span>
            @endif
            @if(str_contains($aktivitas, 'penyuluhan') || str_contains($aktivitas, 'kesehatan'))
              <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">Penyuluhan Kesehatan</span>
            @endif
            @if(str_contains($aktivitas, 'balita') || str_contains($aktivitas, 'ibu'))
              <span class="px-3 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">Pemeriksaan Ibu Hamil & Bayi</span>
            @endif
            @if(str_contains($aktivitas, 'cek_tht_kesehatan_paru'))
              <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">Cek THT dan Kesehatan Paru</span>
            @endif
            @if(str_contains($aktivitas, 'pemeriksaan_tekanan_darah_berat_badan'))
              <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Pemeriksaan Tekanan Darah & Berat Badan</span>
            @endif
            @if(str_contains($aktivitas, 'edukasi'))
              <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Penyuluhan Gaya Hidup Sehat</span>
            @endif
          </div>

          <!-- Stats -->
          <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
            <div class="flex items-center space-x-1">
              <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
              <span>{{ $kegiatan->jumlah_peserta ?? 0 }} peserta</span>
            </div>
            <div class="flex items-center space-x-1">
              <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <span>{{ $kegiatan->penanggung_jawab ?? 'Petugas' }}</span>
            </div>
          </div>

          <!-- Action Button -->
          <a href="{{ route('berita-kesehatan.detail', $kegiatan->keg_kesehatan_id) }}"
             class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-sm group">
            Baca Selengkapnya
            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </a>
        </div>
        </article>
        @endforeach
      </div>
      @endforeach
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="col-span-full text-center py-12">
          <div class="text-gray-400 mb-4">
            <i class="fas fa-newspaper text-6xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada kegiatan kesehatan</h3>
          <p class="text-gray-500">Belum ada kegiatan kesehatan yang tersedia untuk ditampilkan.</p>
        </div>
      </div>
    @endif

    <!-- Pagination -->
    @if($kegiatanKesehatan->hasPages())
    <div class="flex justify-center">
      <nav class="flex items-center space-x-2">
        {{-- Previous Page Link --}}
        @if ($kegiatanKesehatan->onFirstPage())
          <span class="px-3 py-2 text-gray-400 cursor-not-allowed">
            <i class="fas fa-chevron-left"></i>
          </span>
        @else
          <a href="{{ $kegiatanKesehatan->previousPageUrl() }}"
             class="px-3 py-2 text-gray-600 hover:text-blue-600 transition-colors">
            <i class="fas fa-chevron-left"></i>
          </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($kegiatanKesehatan->getUrlRange(1, $kegiatanKesehatan->lastPage()) as $page => $url)
          @if ($page == $kegiatanKesehatan->currentPage())
            <span class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium">{{ $page }}</span>
          @else
            <a href="{{ $url }}"
               class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">{{ $page }}</a>
          @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($kegiatanKesehatan->hasMorePages())
          <a href="{{ $kegiatanKesehatan->nextPageUrl() }}"
             class="px-3 py-2 text-gray-600 hover:text-blue-600 transition-colors">
            <i class="fas fa-chevron-right"></i>
          </a>
        @else
          <span class="px-3 py-2 text-gray-400 cursor-not-allowed">
            <i class="fas fa-chevron-right"></i>
          </span>
        @endif
      </nav>
    </div>
    @endif
  </main>

  @include('partials.footer')

  <style>
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>

  <script>
</body>

</html>
