@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAWAR - Sistem Informasi Warga RW 10</title>
  @vite('resources/css/app.css')
  @vite('resources/css/landingpage.css')
</head>
<body class="font-sans antialiased text-gray-800">
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
        <a class="font-semibold text-gray-700 hover:text-[#133396] transition-colors" href="#">Pengaduan</a>
        <a class="font-semibold text-gray-700 hover:text-[#133396] transition-colors" href="#Galeri">Kegiatan</a>
        <a class="font-semibold text-gray-700 hover:text-[#133396] transition-colors" href="#">Pengajuan Surat</a>
      </nav>

      <!-- Desktop Login Button -->
      <div class="hidden lg:flex items-center gap-4">
        <button class="h-11 min-w-[100px] rounded-lg px-5 text-base font-bold text-white transition-all bg-[#4c67b8] hover:bg-[#2E4EAE]">Masuk</button>
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
        <a href="#" class="block py-2 px-3 text-gray-700 hover:text-[#133396] hover:bg-gray-50 rounded-md transition-colors font-medium">Pengaduan</a>
        <a href="#" class="block py-2 px-3 text-gray-700 hover:text-[#133396] hover:bg-gray-50 rounded-md transition-colors font-medium">Kegiatan</a>
        <a href="#" class="block py-2 px-3 text-gray-700 hover:text-[#133396] hover:bg-gray-50 rounded-md transition-colors font-medium">Pengajuan Surat</a>
        <div class="pt-3 border-t border-gray-200">
          <button class="w-full h-11 rounded-lg px-5 text-base font-bold text-white transition-all bg-[#133396] hover:bg-[#2E4EAE]">Masuk</button>
        </div>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="relative bg-gradient-to-r from-[#596fb4] to-[#94e9b5] text-white py-20">
    <div class="absolute inset-0" style="background-image: url('{{ asset('storage/img/bg.jpg') }}'); background-size: cover; background-position: center; opacity: 0.2;"></div>
    <div class="container mx-auto px-4 relative z-10">
      <div class="max-w-3xl mx-auto text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Selamat Datang,<br>Layanan Warga RW 10</h1>
        <p class="text-lg mb-8">Sistem informasi terpadu untuk memudahkan pengelolaan administrasi, iuran, dan kegiatan warga di lingkungan RW 010 Tanah Baru, Beji.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <a href="#" class="bg-green-500 hover:bg-green-600 text-white font-medium px-6 py-3 rounded-md transition">Buat Permohonan</a>
          <a href="#" class="bg-white hover:bg-gray-100 text-blue-800 font-medium px-6 py-3 rounded-md transition">Lapor Pengaduan</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Galeri Kegiatan Section -->
  <section class="py-16 bg-gradient-to-b from-blue-50 to-green-50" id="Galeri">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-center mb-3">Galeri Kegiatan Warga RW 10</h2>
      <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">Dokumentasi berbagai kegiatan dan acara yang telah dilaksanakan di lingkungan RW 10.</p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 justify-items-center">
        @forelse($kegiatans as $kegiatan)
        <!-- Kegiatan Card -->
         @php
          $borderClass = match($kegiatan->jenis_kegiatan) {
            'karang_taruna' => 'border-3 border-[#00b8db]',
            'posyandu' => 'border-3 border-[#fb64b6]',
            'umum' => 'border-3 border-[#90a1b9]',
            'Posmaja' => 'border-3 border-[#39E079]',
          };
        @endphp
        <div class="gallery-card {{ $borderClass }}">
        @php
          $badgeClass = match($kegiatan->jenis_kegiatan) {
            'karang_taruna' => 'text-white bg-[#00b8db] px-2 py-1 rounded-full text-xs shadow-lg',
            'posyandu' => 'text-white bg-[#fb64b6] px-2 py-1 rounded-full text-xs shadow-lg',
            'umum' => 'text-white bg-[#90a1b9] px-2 py-1 rounded-full text-xs shadow-lg',
            'Posmaja' => 'text-white bg-[#39E079] px-2 py-1 rounded-full text-xs shadow-lg',
          };
        @endphp
          @if($kegiatan->foto_kegiatan)
            <div class="gallery-card__image">
              <img src="{{ Storage::url($kegiatan->foto_kegiatan) }}" alt="{{ $kegiatan->nama_kegiatan }}">
            </div>
          @else
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M20 5H4V19L13.2923 9.70649C13.6828 9.31595 14.3159 9.31591 14.7065 9.70641L20 15.0104V5ZM2 3.9934C2 3.44476 2.45531 3 2.9918 3H21.0082C21.556 3 22 3.44495 22 3.9934V20.0066C22 20.5552 21.5447 21 21.0082 21H2.9918C2.44405 21 2 20.5551 2 20.0066V3.9934ZM8 11C6.89543 11 6 10.1046 6 9C6 7.89543 6.89543 7 8 7C9.10457 7 10 7.89543 10 9C10 10.1046 9.10457 11 8 11Z"></path>
            </svg>
          @endif
          <div class="gallery-card__content">
            <p class="gallery-card__title">{{ $kegiatan->nama_kegiatan }}</p>
            <p class="gallery-card__description">{{\Illuminate\Support\Str::words(($kegiatan->deskripsi), 18, '...')}}</p>
            <div class="mt-2 flex justify-between items-center text-sm">
              <span class="{{ $badgeClass}}">
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
        @empty
        <!-- Default cards when no activities exist -->
        <div class="gallery-card">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M20 5H4V19L13.2923 9.70649C13.6828 9.31595 14.3159 9.31591 14.7065 9.70641L20 15.0104V5ZM2 3.9934C2 3.44476 2.45531 3 2.9918 3H21.0082C21.556 3 22 3.44495 22 3.9934V20.0066C22 20.5552 21.5447 21 21.0082 21H2.9918C2.44405 21 2 20.5551 2 20.0066V3.9934ZM8 11C6.89543 11 6 10.1046 6 9C6 7.89543 6.89543 7 8 7C9.10457 7 10 7.89543 10 9C10 10.1046 9.10457 11 8 11Z"></path>
          </svg>
          <div class="gallery-card__content">
            <p class="gallery-card__title">Belum Ada Kegiatan</p>
            <p class="gallery-card__description">Kegiatan warga akan ditampilkan di sini setelah data diinput melalui sistem.</p>
          </div>
        </div>
        @endforelse
      </div>

      <!-- Tombol Eksplor Galeri -->
      <div class="text-center mt-12">
        <a href="{{ route('galeri') }}" class="inline-flex items-center gap-2 bg-[#4c67b8] hover:bg-[#2E4EAE] text-white font-semibold px-8 py-3 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
          Eksplor Galeri Lengkap
        </a>
      </div>
    </div>
  </section>

  <!-- Struktur Organisasi Section -->
  <section class="py-16 relative bg-green-50">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-center mb-3 text-black">Bagan Struktur RT & RW</h2>
      <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">Mengenal lebih dekat para pengurus RT dan RW yang berkomitmen melayani warga.</p>

      <!-- RW Level Structure -->
      <div class="mb-6">
        <div class="border-2 border-blue-200 rounded-lg p-8 bg-white shadow-lg max-w-4xl mx-auto">
          <!-- Ketua RW at top center -->
          <div class="flex justify-center mb-4">
            @if($rwStructure['ketua'])
            <div class="bg-blue-50 rounded-xl shadow-md p-4 w-56 text-center border-2 border-blue-300">
              <div class="w-16 h-16 rounded-full bg-gray-200 mx-auto mb-3 overflow-hidden">
                @if($rwStructure['ketua']->foto)
                  <img src="{{ Storage::url($rwStructure['ketua']->foto) }}" alt="{{ $rwStructure['ketua']->jabatan }}" class="w-full h-full object-cover">
                @else
                  <img src="https://ui-avatars.com/api/?name={{ urlencode($rwStructure['ketua']->warga ? $rwStructure['ketua']->warga->nama : $rwStructure['ketua']->nama) }}&background=3b82f6&color=ffffff&size=64" alt="{{ $rwStructure['ketua']->jabatan }}" class="w-full h-full object-cover">
                @endif
              </div>
              <h3 class="font-bold text-sm">{{ $rwStructure['ketua']->warga ? $rwStructure['ketua']->warga->nama : $rwStructure['ketua']->nama }}</h3>
              <p class="text-blue-600 font-medium text-xs">{{ $rwStructure['ketua']->jabatan }}</p>
              <p class="text-gray-500 text-xs">{{ $rwStructure['ketua']->periode_mulai }} - {{ $rwStructure['ketua']->periode_selesai }}</p>
            </div>
            @else
            <div class="bg-gray-100 rounded-xl shadow-md p-4 w-56 text-center border-2 border-gray-300">
              <div class="w-16 h-16 rounded-full bg-gray-300 mx-auto mb-3"></div>
              <p class="text-gray-500 text-sm">Ketua RW</p>
              <p class="text-gray-400 text-xs">Belum ada data</p>
            </div>
            @endif
          </div>

          <!-- Connection lines -->
          <div class="flex flex-col relative items-center mb-4">
            <div class="w-[2px] h-8 bg-gray-400 mx-auto"></div>
            <div class="relative w-24 h-[2px] bg-gray-400">
             <span class="absolute top-0 left-0 w-[2px] h-4 bg-gray-400"></span>
             <span class="absolute top-0 right-0 w-[2px] h-4 bg-gray-400"></span>
             </div>
          </div>

          <!-- Sekretaris and Bendahara RW -->
          <div class="flex justify-center gap-8">
            <!-- Sekretaris RW -->
            @if($rwStructure['sekretaris'])
            <div class="bg-green-50 rounded-xl shadow-md p-4 w-48 text-center border-2 border-green-300">
              <div class="w-14 h-14 rounded-full bg-gray-200 mx-auto mb-3 overflow-hidden">
                @if($rwStructure['sekretaris']->foto)
                  <img src="{{ Storage::url($rwStructure['sekretaris']->foto) }}" alt="{{ $rwStructure['sekretaris']->jabatan }}" class="w-full h-full object-cover">
                @else
                  <img src="https://ui-avatars.com/api/?name={{ urlencode($rwStructure['sekretaris']->warga ? $rwStructure['sekretaris']->warga->nama : $rwStructure['sekretaris']->nama) }}&background=10b981&color=ffffff&size=56" alt="{{ $rwStructure['sekretaris']->jabatan }}" class="w-full h-full object-cover">
                @endif
              </div>
              <h3 class="font-bold text-sm">{{ $rwStructure['sekretaris']->warga ? $rwStructure['sekretaris']->warga->nama : $rwStructure['sekretaris']->nama }}</h3>
              <p class="text-green-600 font-medium text-xs">{{ $rwStructure['sekretaris']->jabatan }}</p>
              <p class="text-gray-500 text-xs">{{ $rwStructure['sekretaris']->periode_mulai }} - {{ $rwStructure['sekretaris']->periode_selesai }}</p>
            </div>
            @else
            <div class="bg-gray-100 rounded-xl shadow-md p-4 w-48 text-center border-2 border-gray-300">
              <div class="w-14 h-14 rounded-full bg-gray-300 mx-auto mb-3"></div>
              <p class="text-gray-500 text-sm">Sekretaris RW</p>
              <p class="text-gray-400 text-xs">Belum ada data</p>
            </div>
            @endif

            <!-- Bendahara RW -->
            @if($rwStructure['bendahara'])
            <div class="bg-yellow-50 rounded-xl shadow-md p-4 w-48 text-center border-2 border-yellow-300">
              <div class="w-14 h-14 rounded-full bg-gray-200 mx-auto mb-3 overflow-hidden">
                @if($rwStructure['bendahara']->foto)
                  <img src="{{ Storage::url($rwStructure['bendahara']->foto) }}" alt="{{ $rwStructure['bendahara']->jabatan }}" class="w-full h-full object-cover">
                @else
                  <img src="https://ui-avatars.com/api/?name={{ urlencode($rwStructure['bendahara']->warga ? $rwStructure['bendahara']->warga->nama : $rwStructure['bendahara']->nama) }}&background=f59e0b&color=ffffff&size=56" alt="{{ $rwStructure['bendahara']->jabatan }}" class="w-full h-full object-cover">
                @endif
              </div>
              <h3 class="font-bold text-sm">{{ $rwStructure['bendahara']->warga ? $rwStructure['bendahara']->warga->nama : $rwStructure['bendahara']->nama }}</h3>
              <p class="text-yellow-600 font-medium text-xs">{{ $rwStructure['bendahara']->jabatan }}</p>
              <p class="text-gray-500 text-xs">{{ $rwStructure['bendahara']->periode_mulai }} - {{ $rwStructure['bendahara']->periode_selesai }}</p>
            </div>
            @else
            <div class="bg-gray-100 rounded-xl shadow-md p-4 w-48 text-center border-2 border-gray-300">
              <div class="w-14 h-14 rounded-full bg-gray-300 mx-auto mb-3"></div>
              <p class="text-gray-500 text-sm">Bendahara RW</p>
              <p class="text-gray-400 text-xs">Belum ada data</p>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Connection to RT Level -->
       <div class="flex flex-col relative items-center mb-4">
            <div class="w-[2px] h-10 bg-gray-400"></div>
            <div class="relative w-[50%] h-[2px] bg-gray-400">
                <span class="absolute top-0 left-0 w-[2px] h-10 bg-gray-400"></span>
                <span class="absolute top-0 left-1/2 -translate-x-1/2 w-[2px] h-10 bg-gray-400"></span>
                <span class="absolute top-0 right-0 w-[2px] h-10 bg-gray-400"></span>
            </div>
        </div>

      <!-- RT Level Structures -->
      @if(count($rtStructures) > 0)
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto mt-12">
        @foreach($rtStructures as $rtNumber => $rtStructure)
        <div class="border-2 border-purple-200 rounded-xl p-6 bg-white shadow-lg">
          <!-- Ketua RT at top center -->
          <div class="flex justify-center mb-1">
            @if($rtStructure['ketua'])
            <div class="bg-purple-50 rounded-xl shadow-md p-4 w-48 text-center border-2 border-purple-300">
              <div class="w-14 h-14 rounded-full bg-gray-200 mx-auto mb-3 overflow-hidden">
                @if($rtStructure['ketua']->foto)
                  <img src="{{ Storage::url($rtStructure['ketua']->foto) }}" alt="{{ $rtStructure['ketua']->jabatan }}" class="w-full h-full object-cover">
                @else
                  <img src="https://ui-avatars.com/api/?name={{ urlencode($rtStructure['ketua']->nama) }}&background=8b5cf6&color=ffffff&size=56" alt="{{ $rtStructure['ketua']->jabatan }}" class="w-full h-full object-cover">
                @endif
              </div>
              <h3 class="font-bold text-sm">{{ $rtStructure['ketua']->nama }}</h3>
              <p class="text-purple-600 font-medium text-xs">{{ $rtStructure['ketua']->jabatan }}</p>
              <p class="text-gray-500 text-xs">{{ $rtStructure['ketua']->periode_mulai }} - {{ $rtStructure['ketua']->periode_selesai }}</p>
            </div>
            @endif
          </div>

          <!-- Connection lines for RT -->
          <div class="flex flex-col relative items-center mb-4">
            <div class="w-[2px] h-8 bg-gray-400 mx-auto"></div>
            <div class="relative w-24 h-[2px] bg-gray-400">
             <span class="absolute top-0 left-0 w-[2px] h-4 bg-gray-400"></span>
             <span class="absolute top-0 right-0 w-[2px] h-4 bg-gray-400"></span>
             </div>
          </div>
                <!-- <div class="w-px h-6 bg-gray-400 absolute top-6 left-3"></div>
                <div class="w-px h-6 bg-gray-400 absolute top-6 right-3"></div> -->
          <!-- Sekretaris and Bendahara RT -->
          <div class="flex justify-center gap-4">
            <!-- Sekretaris RT -->
            @if($rtStructure['sekretaris'])
            <div class="bg-green-50 rounded-xl shadow-md p-3 w-36 text-center border-2 border-green-300">
              <div class="w-12 h-12 rounded-full bg-gray-200 mx-auto mb-2 overflow-hidden">
                @if($rtStructure['sekretaris']->foto)
                  <img src="{{ Storage::url($rtStructure['sekretaris']->foto) }}" alt="{{ $rtStructure['sekretaris']->jabatan }}" class="w-full h-full object-cover">
                @else
                  <img src="https://ui-avatars.com/api/?name={{ urlencode($rtStructure['sekretaris']->nama) }}&background=10b981&color=ffffff&size=48" alt="{{ $rtStructure['sekretaris']->jabatan }}" class="w-full h-full object-cover">
                @endif
              </div>
              <h3 class="font-bold text-xs">{{ $rtStructure['sekretaris']->nama }}</h3>
              <p class="text-green-600 font-medium text-xs">Sekretaris</p>
            </div>
            @else
            <div class="bg-gray-100 rounded-xl shadow-md p-3 w-36 text-center border-2 border-gray-300">
              <div class="w-12 h-12 rounded-full bg-gray-300 mx-auto mb-2"></div>
              <p class="text-gray-500 text-xs">Sekretaris</p>
              <p class="text-gray-400 text-xs">Belum ada</p>
            </div>
            @endif

            <!-- Bendahara RT -->
            @if($rtStructure['bendahara'])
            <div class="bg-yellow-50 rounded-xl shadow-md p-3 w-36 text-center border-2 border-yellow-300">
              <div class="w-12 h-12 rounded-full bg-gray-200 mx-auto mb-2 overflow-hidden">
                @if($rtStructure['bendahara']->foto)
                  <img src="{{ Storage::url($rtStructure['bendahara']->foto) }}" alt="{{ $rtStructure['bendahara']->jabatan }}" class="w-full h-full object-cover">
                @else
                  <img src="https://ui-avatars.com/api/?name={{ urlencode($rtStructure['bendahara']->nama) }}&background=f59e0b&color=ffffff&size=48" alt="{{ $rtStructure['bendahara']->jabatan }}" class="w-full h-full object-cover">
                @endif
              </div>
              <h3 class="font-bold text-xs">{{ $rtStructure['bendahara']->nama }}</h3>
              <p class="text-yellow-600 font-medium text-xs">Bendahara</p>
            </div>
            @else
            <div class="bg-gray-100 rounded-xl shadow-md p-3 w-36 text-center border-2 border-gray-300">
              <div class="w-12 h-12 rounded-full bg-gray-300 mx-auto mb-2"></div>
              <p class="text-gray-500 text-xs">Bendahara</p>
              <p class="text-gray-400 text-xs">Belum ada</p>
            </div>
            @endif
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>
  </section>

  <!-- Cara Menggunakan Section -->
  <section class="py-16 bg-gradient-to-b from-green-50 to-white">
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

  <!-- Footer -->
  <footer class="bg-[#4c67b8] text-white pt-12 pb-6 text-center">
    <div class="container mx-auto px-3 flex justify-center">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-8 justify-items-center">
        <!-- Column 1 -->
        <div class="text-start">
          <h3 class="text-xl font-bold mb-4">Tentang SIMAWAR</h3>
          <p class="text-blue-200 mb-4">Sistem Informasi Manajemen Layanan Warga RW 010 Tanah Baru, Beji. Didedikasikan untuk pelayanan yang lebih baik, cepat, dan transparan.</p>
        </div>

        <!-- Column 2 -->
        <div>
          <h3 class="text-xl font-bold mb-4">Layanan Darurat</h3>
          <ul class="space-y-2">
            <li class="flex items-center"><span class="text-blue-300 mr-2">📞</span> Keamanan: 112</li>
            <li class="flex items-center"><span class="text-blue-300 mr-2">📞</span> Ambulans: 118</li>
            <li class="flex items-center"><span class="text-blue-300 mr-2">📞</span> Pemadam: 113</li>
            <li class="flex items-center"><span class="text-blue-300 mr-2">📞</span> Ketua RW: 0812-3456-7890</li>
          </ul>
        </div>

        <!-- Column 3 -->
        <div>
          <h3 class="text-xl font-bold mb-4">Temukan Kami</h3>
          <ul class="space-y-2">
            <li><a href="#" class="text-blue-200 hover:text-white">Instagram</a></li>
            <li><a href="#" class="text-blue-200 hover:text-white">Email</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="border-t border-blue-800 pt-6 text-center">
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
