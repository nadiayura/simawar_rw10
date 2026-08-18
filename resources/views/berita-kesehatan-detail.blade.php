@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
@endphp
<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $berita->nama_kegiatan }} - SIMAWAR</title>
  <link rel="icon" type="image/png" href="{{ \Illuminate\Support\Facades\Storage::url('logo/logoutama.png') }}">
  @vite('resources/css/app.css')
  @vite('resources/css/landingpage.css')
</head>

<body class="font-sans antialiased text-gray-800 bg-gray-50">
  @include('partials.navbar')

  <!-- Main Content -->
  <main class="py-8 bg-gradient-to-b from-blue-100 to-white">
    <div class="container mx-auto px-4">
      <div class="max-w-4xl mx-auto">
        <!-- Header Article -->
        <header class="mb-8">
          <div class="flex items-center gap-4 mb-4">
            @php
            $jenisClass = match($berita->jenis_kegiatan) {
              'posyandu' => 'bg-pink-100 text-pink-800',
              'posmaja' => 'bg-green-100 text-green-800',
              default => 'bg-amber-100 text-blue-800'
            };
            @endphp
            <span class="px-3 py-1 text-sm font-medium rounded-full {{ $jenisClass }}">
              {{ $berita->jenis_kegiatan === 'posyandu' ? 'Posyandu' : ($berita->jenis_kegiatan === 'posmaja' ? 'Posmaja' : ucfirst($berita->jenis_kegiatan)) }}
            </span>
            <span class="text-gray-500 text-sm">{{ $berita->tgl->format('d F Y') }}</span>
          </div>

          <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $berita->nama_kegiatan }}</h1>

          <!-- Meta Info -->
          <div class="flex flex-wrap items-center gap-6 text-sm text-gray-600 bg-white rounded-lg p-4">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
              <span><strong>{{ $berita->jumlah_peserta }}</strong> peserta</span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <span><strong>Penanggung Jawab:</strong> {{ $berita->penanggung_jawab }}</span>
            </div>
            @if($berita->status)
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <span><strong>Status:</strong> {{ $berita->status?->keterangan }}</span>
            </div>
            @endif
          </div>
        </header>

        <!-- Dokumentasi -->
        @if($berita->dokumentasi && is_array($berita->dokumentasi) && count($berita->dokumentasi) > 0)
        <section class="  rounded-lg p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($berita->dokumentasi as $index => $foto)
            <div class="aspect-video bg-gray-200 rounded-lg overflow-hidden cursor-pointer" onclick="openImageModal('{{ Storage::url($foto) }}', '{{ $berita->nama_kegiatan }}', '{{ $index }}')">
              <img src="{{ Storage::url($foto) }}" alt="Dokumentasi {{ $berita->nama_kegiatan }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
            </div>
            @endforeach
          </div>
        </section>
        @endif

        <!-- Content -->
        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8">
          <!-- Rangkuman Kegiatan -->
          <section class="">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Rangkuman Kegiatan</h2>

            <!-- Statistics Cards -->
             @if($berita->rincian_peserta && is_array($berita->rincian_peserta))
             @php
               $rincianPeserta = $berita->rincian_peserta;
               $totalPeserta = $berita->jumlah_peserta ?? 0;

               // Filter data yang tidak 0
               $validData = [];

               if (isset($rincianPeserta['bayi']) && $rincianPeserta['bayi'] > 0) {
                   $validData[] = ['label' => 'Bayi', 'jumlah' => $rincianPeserta['bayi'], 'color' => 'from-pink-400 to-pink-500'];
               }

               if (isset($rincianPeserta['ibu_hamil']) && $rincianPeserta['ibu_hamil'] > 0) {
                   $validData[] = ['label' => 'Ibu Hamil', 'jumlah' => $rincianPeserta['ibu_hamil'], 'color' => 'from-purple-400 to-purple-500'];
               }

               if (isset($rincianPeserta['remaja']) && $rincianPeserta['remaja'] > 0) {
                   $validData[] = ['label' => 'Remaja', 'jumlah' => $rincianPeserta['remaja'], 'color' => 'from-blue-400 to-blue-500'];
               }

               if (isset($rincianPeserta['anak']) && $rincianPeserta['anak'] > 0) {
                   $validData[] = ['label' => 'Anak', 'jumlah' => $rincianPeserta['anak'], 'color' => 'from-green-400 to-green-500'];
               }

               // Selalu tampilkan total jika ada data
               if ($totalPeserta > 0) {
                   $validData[] = ['label' => 'Total Peserta', 'jumlah' => $totalPeserta, 'color' => 'from-gray-400 to-gray-500'];
               }
             @endphp

             @if(count($validData) > 0)
             <div class="grid grid-cols-1 md:grid-cols-{{ min(count($validData), 4) }} gap-4 mb-6">
               @foreach($validData as $data)
               <div class="bg-gradient-to-r {{ $data['color'] }} rounded-lg p-4 text-white">
                 <div class="text-sm font-medium opacity-90">{{ $data['label'] }}</div>
                 <div class="text-3xl font-bold">{{ number_format($data['jumlah'], 0, ',', '.') }}</div>
               </div>
               @endforeach
             </div>
             @endif

            @endif

             <!-- Category Tags berdasarkan jenis kegiatan -->
             <div class="flex flex-wrap gap-2 mb-6">
               @php
               $aktivitas = strtolower($berita->aktivitas_dilakukan ?? '');
               @endphp

               @if(str_contains($aktivitas, 'imunisasi') || str_contains($aktivitas, 'vaksin'))
                 <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">Imunisasi</span>
               @endif

               @if(str_contains($aktivitas, 'vitamin') || str_contains($aktivitas, 'suplemen'))
                 <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">Pemberian Vitamin</span>
               @endif

               @if(str_contains($aktivitas, 'penyuluhan') || str_contains($aktivitas, 'kesehatan'))
                 <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm font-medium rounded-full">Penyuluhan Kesehatan</span>
               @endif

               @if(str_contains($aktivitas, 'balita') || str_contains($aktivitas, 'Ibu'))
                 <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">Pemeriksaan Ibu Hamil & Bayi</span>
               @endif

               @if(str_contains($aktivitas, 'cek'))
                 <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">Cek THT dan Kesehatan Paru</span>
               @endif

               @if(str_contains($aktivitas, 'pemeriksaan'))
                 <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Pemeriksaan Tekanan Darah & Berat Badan</span>
               @endif

               @if(str_contains($aktivitas, 'edukasi'))
                 <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">Edukasi Gaya Hidup Sehat</span>
               @endif
             </div>
          </section>



          <!-- Hasil Pelaksanaan -->
          @if($berita->hasil_pelaksanaan)
          <section class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Hasil Pelaksanaan
            </h2>
            <div class="prose max-w-none text-gray-700 leading-relaxed bg-white rounded-lg">
              {!! nl2br(e($berita->hasil_pelaksanaan)) !!}
            </div>
          </section>
          @endif
        </div>

        <!-- Back Button -->
        <div class="mt-8 text-center">
          <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 bg-[#4c67b8] hover:bg-[#2E4EAE] text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Beranda
          </a>
        </div>
      </div>
    </div>
  </main>

  @include('partials.footer')

  <!-- Modal untuk menampilkan gambar besar -->
  <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
      <!-- Tombol close -->
      <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>

      <!-- Gambar -->
      <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">

      <!-- Navigasi gambar (jika ada lebih dari 1 gambar) -->
      <div id="imageNavigation" class="absolute inset-y-0 left-0 right-0 flex items-center justify-between px-4">
        <button id="prevButton" onclick="navigateImage(-1)" class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        <button id="nextButton" onclick="navigateImage(1)" class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>

      <!-- Info gambar -->
      <div class="absolute bottom-4 left-4 right-4 text-white text-center">
        <p id="imageInfo" class="bg-black bg-opacity-50 px-4 py-2 rounded-lg"></p>
      </div>
    </div>
  </div>

  <script>
    let currentImageIndex = 0;
    let imageList = [];
    let activityName = '';

    // Inisialisasi daftar gambar
    @if($berita->dokumentasi && is_array($berita->dokumentasi) && count($berita->dokumentasi) > 0)
    imageList = [
      @foreach($berita->dokumentasi as $foto)
      '{{ Storage::url($foto) }}',
      @endforeach
    ];
    activityName = '{{ $berita->nama_kegiatan }}';
    @endif

    function openImageModal(imageSrc, title, index) {
      currentImageIndex = index;
      const modal = document.getElementById('imageModal');
      const modalImage = document.getElementById('modalImage');
      const imageInfo = document.getElementById('imageInfo');
      const navigation = document.getElementById('imageNavigation');

      modalImage.src = imageSrc;
      modalImage.alt = title;
      imageInfo.textContent = `${title} - Gambar ${index + 1} dari ${imageList.length}`;

      // Tampilkan/sembunyikan navigasi berdasarkan jumlah gambar
      if (imageList.length > 1) {
        navigation.style.display = 'flex';
        updateNavigationButtons();
      } else {
        navigation.style.display = 'none';
      }

      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
      const modal = document.getElementById('imageModal');
      modal.classList.add('hidden');
      document.body.style.overflow = 'auto';
    }

    function navigateImage(direction) {
      currentImageIndex += direction;

      if (currentImageIndex < 0) {
        currentImageIndex = imageList.length - 1;
      } else if (currentImageIndex >= imageList.length) {
        currentImageIndex = 0;
      }

      const modalImage = document.getElementById('modalImage');
      const imageInfo = document.getElementById('imageInfo');

      modalImage.src = imageList[currentImageIndex];
      imageInfo.textContent = `${activityName} - Gambar ${currentImageIndex + 1} dari ${imageList.length}`;

      updateNavigationButtons();
    }

    function updateNavigationButtons() {
      const prevButton = document.getElementById('prevButton');
      const nextButton = document.getElementById('nextButton');

      // Sembunyikan tombol jika hanya ada 1 gambar
      if (imageList.length <= 1) {
        prevButton.style.display = 'none';
        nextButton.style.display = 'none';
      } else {
        prevButton.style.display = 'block';
        nextButton.style.display = 'block';
      }
    }

    // Tutup modal dengan tombol Escape
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeImageModal();
      } else if (event.key === 'ArrowLeft') {
        navigateImage(-1);
      } else if (event.key === 'ArrowRight') {
        navigateImage(1);
      }
    });

    // Tutup modal ketika mengklik area di luar gambar
    document.getElementById('imageModal').addEventListener('click', function(event) {
      if (event.target === this) {
        closeImageModal();
      }
    });
  </script>
</body>

</html>
