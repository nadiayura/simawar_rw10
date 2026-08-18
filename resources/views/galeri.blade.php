@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galeri Kegiatan - SIMAWAR RW 10</title>
  <link rel="icon" type="image/png" href="{{ \Illuminate\Support\Facades\Storage::url('logo/logoutama.png') }}">
  @vite('resources/css/app.css')
  @vite('resources/css/landingpage.css')
</head>
<body class="font-sans antialiased text-gray-800 bg-gray-50">
  @include('partials.navbar')

  <!-- Page Header -->
  <section class="bg-gradient-to-b from-[#4c70b8] to-white text-gray-800 py-16">
    <div class="container mx-auto px-4">
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

  <!-- Filter Section -->
  <section class="py-8 bg-white ">
    <div class="container mx-auto px-4">
      <form method="GET" action="{{ route('galeri') }}" class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- Search Bar -->
        <div class="flex-1 max-w-md">
          <div class="relative">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari kegiatan..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#133396] focus:border-transparent">
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>

        <!-- Filter Buttons -->
                 <!-- Search Button -->
        <button type="submit" class="bg-[#4C67B8] hover:bg-[#a7b6e2] text-white px-6 py-2 rounded-lg font-medium transition-colors">
          Cari
        </button>
      </form>
    </div>
  </section>

  <section class="py-8 bg-white">
    <div class="container mx-auto px-4">
      @if($kegiatans->count() > 0)
        @foreach($groupedKegiatans as $monthKey => $items)
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
              {{ $monthLabels[$monthKey] ?? $monthKey }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 ">
              @foreach($items as $kegiatan)
                <div class="gallery-card">
                  <a href="{{ route('galeri.detail', $kegiatan->keg_karang_taruna_id) }}" class="block h-full">
                    @if($kegiatan->foto_kegiatan)
                      <div class="gallery-card__image">
                        <img src="{{ Storage::url($kegiatan->foto_kegiatan) }}" alt="{{ $kegiatan->nama_kegiatan }}">
                      </div>
                    @else
                      <div class="gallery-card__image flex items-center justify-center bg-gray-100">
                        <svg class="w-16 h-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M20 5H4V19L13.2923 9.70649C13.6828 9.31595 14.3159 9.31591 14.7065 9.70641L20 15.0104V5ZM2 3.9934C2 3.44476 2.45531 3 2.9918 3H21.0082C21.556 3 22 3.44495 22 3.9934V20.0066C22 20.5552 21.5447 21 21.0082 21H2.9918C2.44405 21 2 20.5551 2 20.0066V3.9934ZM8 11C6.89543 11 6 10.1046 6 9C6 7.89543 6.89543 7 8 7C9.10457 7 10 7.89543 10 9C10 10.1046 9.10457 11 8 11Z"></path>
                        </svg>
                      </div>
                    @endif

                    <div class="gallery-card__content">
                      <p class="gallery-card__title">{{ $kegiatan->nama_kegiatan }}</p>
                      <p class="gallery-card__description">{{ Str::words($kegiatan->deskripsi, 15, '...') }}</p>
                      <div class="mt-2 flex justify-between items-center text-sm">
                        <span>
                          {{ $kegiatan->jenis_kegiatan_label }}
                        </span>
                        <span class="text-white text-xs font-medium">{{ $kegiatan->tanggal->format('d M Y') }}</span>
                      </div>
                    </div>
                  </a>
                </div>
              @endforeach
            </div>
          </div>
        @endforeach
        <div class="mt-12">
          {{ $kegiatans->appends(request()->query())->links() }}
        </div>
      @else
        <!-- Empty State -->
        <div class="text-center py-16">
          <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
          <h3 class="mt-4 text-lg font-medium text-gray-900">Tidak ada kegiatan ditemukan</h3>
          <p class="mt-2 text-gray-500">
            @if(request('search') || request('jenis'))
              Coba ubah filter atau kata kunci pencarian Anda.
            @else
              Belum ada kegiatan yang tersedia saat ini.
            @endif
          </p>
          @if(request('search') || request('jenis'))
            <a href="{{ route('galeri') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[#133396] hover:bg-[#2E4EAE]">
              Lihat Semua Kegiatan
            </a>
          @endif
        </div>
      @endif
    </div>
  </section>

  @include('partials.footer')
</body>
</html>
