@php
use Illuminate\Support\Facades\Storage;

$pjRtId = optional($kegiatan->pjWarga)->no_rt_id;
$pjRtLabel = null;
if ($pjRtId) {
    $pjRtLabel = str_starts_with((string) $pjRtId, 'RT-')
        ? str_replace('-', ' ', (string) $pjRtId)
        : 'RT '.str_pad((string) $pjRtId, 3, '0', STR_PAD_LEFT);
}
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $kegiatan->nama_kegiatan }} - SIMAWAR</title>
  <link rel="icon" type="image/png" href="{{ \Illuminate\Support\Facades\Storage::url('logo/logoutama.png') }}">
  @vite('resources/css/app.css')
  @vite('resources/css/landingpage.css')
</head>
<body class="font-sans antialiased text-gray-800 bg-gray-50">
  @include('partials.navbar')

  <main class="py-8 bg-gradient-to-b from-blue-100 to-white">
    <div class="container mx-auto px-4">
      <div class="max-w-4xl mx-auto">
        <header class="mb-8">
          <div class="flex items-center gap-4 mb-4">
            <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
              {{ $kegiatan->jenis_kegiatan_label }}
            </span>
            <span class="text-gray-500 text-sm">
              {{ optional($kegiatan->tanggal)->translatedFormat('d F Y') }}
            </span>
            @if($kegiatan->status)
              <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                {{ $kegiatan->status?->keterangan }}
              </span>
            @endif
          </div>

          <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            {{ $kegiatan->nama_kegiatan }}
          </h1>

          <div class="flex flex-wrap items-center gap-6 text-sm text-gray-600 bg-white rounded-lg p-4">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <span>
                <span class="font-semibold">Penanggung Jawab:</span>
                {{ optional($kegiatan->pjWarga)->nama ?? $kegiatan->penanggung_jawab ?? '-' }}
                @if($pjRtLabel)
                  <span class="ml-2 text-sm text-gray-500">({{ $pjRtLabel }})</span>
                @endif
              </span>
            </div>
          </div>
        </header>

        @if($kegiatan->dokumentasi && is_array($kegiatan->dokumentasi) && count($kegiatan->dokumentasi) > 0)
          <section class="rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              @foreach($kegiatan->dokumentasi as $index => $foto)
                <div class="aspect-video bg-gray-200 rounded-lg overflow-hidden cursor-pointer"
                     onclick="openImageModal('{{ Storage::url($foto) }}', '{{ $kegiatan->nama_kegiatan }}', '{{ $index }}')">
                  <img src="{{ Storage::url($foto) }}"
                       alt="Dokumentasi {{ $kegiatan->nama_kegiatan }}"
                       class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                </div>
              @endforeach
            </div>
          </section>
        @endif

        <div class="bg-white rounded-lg shadow-sm p-6 md:p-8 mt-6">
          <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
              Deskripsi Kegiatan
            </h2>
            <div class="prose max-w-none text-gray-700 leading-relaxed">
              {!! nl2br(e($kegiatan->deskripsi)) !!}
            </div>
          </section>
        </div>

        <div class="mt-8 text-center">
          <a href="{{ route('galeri') }}"
             class="inline-flex items-center gap-2 bg-[#4c67b8] hover:bg-[#2E4EAE] text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Galeri
          </a>
        </div>
      </div>
    </div>
  </main>

  @include('partials.footer')
  <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
      <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
      <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
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
      <div class="absolute bottom-4 left-4 right-4 text-white text-center">
        <p id="imageInfo" class="bg-black bg-opacity-50 px-4 py-2 rounded-lg"></p>
      </div>
    </div>
  </div>

  <script>
    let currentImageIndex = 0;
    let imageList = [];
    let activityName = '';

    @if($kegiatan->dokumentasi && is_array($kegiatan->dokumentasi) && count($kegiatan->dokumentasi) > 0)
    imageList = [
      @foreach($kegiatan->dokumentasi as $foto)
      '{{ Storage::url($foto) }}',
      @endforeach
    ];
    activityName = '{{ $kegiatan->nama_kegiatan }}';
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

      if (imageList.length <= 1) {
        prevButton.style.display = 'none';
        nextButton.style.display = 'none';
      } else {
        prevButton.style.display = 'block';
        nextButton.style.display = 'block';
      }
    }

    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeImageModal();
      } else if (event.key === 'ArrowLeft') {
        navigateImage(-1);
      } else if (event.key === 'ArrowRight') {
        navigateImage(1);
      }
    });

    document.getElementById('imageModal').addEventListener('click', function(event) {
      if (event.target === this) {
        closeImageModal();
      }
    });
  </script>
</body>
</html>
