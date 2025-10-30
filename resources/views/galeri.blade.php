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
  @vite('resources/css/app.css')
  @vite('resources/css/landingpage.css')
</head>
<body class="font-sans antialiased text-gray-800 bg-gray-50">
  <!-- Header -->
  <header class="sticky top-0 z-50 border-b border-gray-200/[.06] bg-white/90 backdrop-blur-sm">
    <div class="container mx-auto flex items-center justify-between px-4 py-3 lg:px-8">
      <div class="flex items-center gap-3">
        <svg class="h-10 w-10 text-[#133396]" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2L2 7l10 5 10-5L12 2zm0 11.5L4.5 9.25 2 8l10 5 10-5-2.5 1.25L12 13.5zM12 22l-10-5 2.5-1.25L12 19.5l7.5-3.75L22 17l-10 5z"></path>
        </svg>
        <h2 class="font-display text-xl font-bold text-gray-900">RW 010 Tanah Baru</h2>
      </div>
      
      <!-- Desktop Navigation -->
      <nav class="hidden items-center gap-8 lg:flex">
        <a class="font-semibold text-gray-700 hover:text-[#133396] transition-colors" href="{{ url('/') }}">Beranda</a>
        <a class="font-semibold text-gray-700 hover:text-[#133396] transition-colors" href="#">Pengaduan</a>
        <a class="font-semibold text-[#133396]" href="{{ route('galeri') }}">Kegiatan</a>
        <a class="font-semibold text-gray-700 hover:text-[#133396] transition-colors" href="#">Pengajuan Surat</a>
      </nav>
      
      <!-- Desktop Login Button -->
      <div class="hidden lg:flex items-center gap-4">
        <button class="h-11 min-w-[100px] rounded-lg px-5 text-base font-bold text-white transition-all bg-[#133396] hover:bg-[#2E4EAE]">Masuk</button>
      </div>
      
      <!-- Mobile Menu Button -->
      <div class="lg:hidden">
        <button id="mobile-menu-button" class="p-2 text-gray-700 hover:text-[#133396] focus:outline-none focus:ring-2 focus:ring-[#133396] focus:ring-opacity-50 rounded-md transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden lg:hidden bg-white border-t border-gray-200">
      <div class="px-4 py-3 space-y-3">
        <a href="{{ url('/') }}" class="block py-2 px-3 text-gray-700 hover:text-[#133396] hover:bg-gray-50 rounded-md transition-colors font-medium">Beranda</a>
        <a href="#" class="block py-2 px-3 text-gray-700 hover:text-[#133396] hover:bg-gray-50 rounded-md transition-colors font-medium">Pengaduan</a>
        <a href="{{ route('galeri') }}" class="block py-2 px-3 text-[#133396] bg-blue-50 rounded-md transition-colors font-medium">Kegiatan</a>
        <a href="#" class="block py-2 px-3 text-gray-700 hover:text-[#133396] hover:bg-gray-50 rounded-md transition-colors font-medium">Pengajuan Surat</a>
        <div class="pt-3 border-t border-gray-200">
          <button class="w-full h-11 rounded-lg px-5 text-base font-bold text-white transition-all bg-[#133396] hover:bg-[#2E4EAE]">Masuk</button>
        </div>
      </div>
    </div>
  </header>

  <!-- Page Header -->
  <section class="bg-gradient-to-r from-[#133396] to-[#39E079] text-white py-16">
    <div class="container mx-auto px-4">
      <div class="max-w-3xl mx-auto text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Galeri Kegiatan Warga RW 10</h1>
        <p class="text-lg mb-8">Dokumentasi lengkap berbagai kegiatan dan acara yang telah dilaksanakan di lingkungan RW 010 Tanah Baru, Beji.</p>
      </div>
    </div>
  </section>

  <!-- Filter Section -->
  <section class="py-8 bg-white border-b border-gray-200">
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
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('galeri') }}" 
             class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ !request('jenis') ? 'bg-[#133396] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Semua
          </a>
          @foreach($jenisKegiatan as $jenis)
            @php
              $label = match($jenis) {
                'karang_taruna' => 'Karang Taruna',
                'posyandu' => 'Posyandu',
                'posbindu' => 'Posbindu', 
                'umum' => 'Umum',
                default => ucfirst($jenis)
              };
              $isActive = request('jenis') === $jenis;
            @endphp
            <a href="{{ route('galeri', ['jenis' => $jenis, 'search' => request('search')]) }}" 
               class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $isActive ? 'bg-[#133396] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
              {{ $label }}
            </a>
          @endforeach
        </div>

        <!-- Search Button -->
        <button type="submit" class="bg-[#133396] hover:bg-[#2E4EAE] text-white px-6 py-2 rounded-lg font-medium transition-colors">
          Cari
        </button>
      </form>
    </div>
  </section>

  <!-- Gallery Grid -->
  <section class="py-16">
    <div class="container mx-auto px-4">
      @if($kegiatans->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 ">
          @foreach($kegiatans as $kegiatan)
            @php
              $cardClass = match($kegiatan->jenis_kegiatan) {
                'karang_taruna' => 'gallery-card--karang-taruna',
                'posyandu' => 'gallery-card--posyandu',
                'Posmaja' => 'gallery-card--posyandu',
                'umum' => 'gallery-card--umum',
                default => 'gallery-card--default'
              };
              
              $badgeClass = match($kegiatan->jenis_kegiatan) {
                'karang_taruna' => 'text-white bg-[#00b8db] px-2 py-1 rounded-full text-xs shadow-lg',
                'posyandu' => 'text-white bg-[#fb64b6] px-2 py-1 rounded-full text-xs shadow-lg',
                'Posmaja' => 'text-white bg-[#fb64b6] px-2 py-1 rounded-full text-xs shadow-lg',
                'umum' => 'text-white bg-[#90a1b9] px-2 py-1 rounded-full text-xs shadow-lg',
                default => 'text-white bg-blue-600 px-2 py-1 rounded-full text-xs shadow-lg'
              };
                $borderClass = match($kegiatan->jenis_kegiatan) {
                'karang_taruna' => 'border-3 border-[#00b8db]',
                'posyandu' => 'border-3 border-[#fb64b6]',
                'umum' => 'border-3 border-[#90a1b9]',
                'Posmaja' => 'border-3 border-[#39E079]',
                };
            @endphp
            
            <div class="gallery-card {{ $cardClass }} {{ $borderClass }}">
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
                  <span class="{{ $badgeClass }}">
                    {{ $kegiatan->jenis_kegiatan_label }}
                  </span>
                  <span class="text-white text-xs font-medium">{{ $kegiatan->tanggal->format('d M Y') }}</span>
                </div>
                @if($kegiatan->tenant)
                  <div class="mt-1 text-xs text-gray-200">
                    {{ $kegiatan->tenant->nama }}
                  </div>
                @endif
              </div>
            </div>
          @endforeach
        </div>

        <!-- Pagination -->
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

  <!-- Footer -->
  <footer class="bg-gradient-to-r from-[#133396] to-[#2E4EAE] text-white py-12">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <div class="flex items-center gap-3 mb-4">
            <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2L2 7l10 5 10-5L12 2zm0 11.5L4.5 9.25 2 8l10 5 10-5-2.5 1.25L12 13.5zM12 22l-10-5 2.5-1.25L12 19.5l7.5-3.75L22 17l-10 5z"></path>
            </svg>
            <h3 class="text-xl font-bold">SIMAWAR RW 10</h3>
          </div>
          <p class="text-blue-200">Sistem Informasi Pengelolaan Layanan Warga RW 010 Tanah Baru, Beji.</p>
        </div>
        <div>
          <h4 class="text-lg font-semibold mb-4">Layanan</h4>
          <ul class="space-y-2 text-blue-200">
            <li><a href="#" class="hover:text-white transition">Pengaduan Warga</a></li>
            <li><a href="#" class="hover:text-white transition">Pengajuan Surat</a></li>
            <li><a href="{{ route('galeri') }}" class="hover:text-white transition">Galeri Kegiatan</a></li>
          </ul>
        </div>
        <div>
          <h4 class="text-lg font-semibold mb-4">Kontak</h4>
          <div class="space-y-2 text-blue-200">
            <p>RW 010 Tanah Baru</p>
            <p>Kecamatan Beji, Depok</p>
            <p>Email: info@rw10tanahbaru.id</p>
          </div>
        </div>
      </div>
    </div>
    <div class="border-t border-blue-800 pt-6 text-center text-blue-300">
      <p>© 2025 SIMAWAR – Sistem Informasi Pengelolaan Layanan Warga RW 010 Tanah Baru. Semua hak dilindungi.</p>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const mobileMenuButton = document.getElementById('mobile-menu-button');
      const mobileMenu = document.getElementById('mobile-menu');

      mobileMenuButton.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
      });

      // Close mobile menu when clicking on a link
      const mobileLinks = mobileMenu.querySelectorAll('a');
      mobileLinks.forEach(link => {
        link.addEventListener('click', function() {
          mobileMenu.classList.add('hidden');
        });
      });
    });
  </script>
</body>
</html>