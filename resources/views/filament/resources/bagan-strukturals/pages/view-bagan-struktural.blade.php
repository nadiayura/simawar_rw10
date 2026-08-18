<link rel="stylesheet" href="{{ asset('css/bagan-struktural.css') }}">


<x-filament-panels::page>
    <!-- Struktur Organisasi Section -->
    <section class="py-16 relative bg-green-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-3 text-black">Bagan Struktur RW</h2>

            <!-- RW Level Structure -->
        <div class="mb-6">
        <div class="border-2 border-blue-200 rounded-lg p-8 bg-white shadow-lg max-w-4xl mx-auto">
          <!-- Ketua RW at top center -->
          <div class="flex justify-center mb-4">
            @if($rwStructure['ketua'])
            <div class="bg-blue-50 rounded-xl shadow-md p-4 w-56 text-center border-2 border-blue-300">
              <div class="w-16 h-16 rounded-full bg-gray-200 mx-auto mb-3 overflow-hidden">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($rwStructure['ketua']->warga ? $rwStructure['ketua']->warga->nama : $rwStructure['ketua']->nama) }}&background=3b82f6&color=ffffff&size=64" alt="{{ $rwStructure['ketua']->jabatan }}" class="w-full h-full object-cover">
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

          <!-- Sekretaris and Bendahara RW -->
          <div class="flex justify-center gap-6">
            <!-- Sekretaris RW -->
            @if($rwStructure['sekretaris'])
            <div class="bg-green-50 rounded-xl shadow-lg p-4 w-56 mr-1.5 text-center border-2 border-green-300">
              <div class="w-16 h-16 rounded-full mx-auto mb-4 overflow-hidden bg-amber-800">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($rwStructure['sekretaris']->warga ? $rwStructure['sekretaris']->warga->nama : $rwStructure['sekretaris']->nama) }}&background=10b981&color=ffffff&size=100"
                     alt="{{ $rwStructure['sekretaris']->jabatan }}"
                     class="w-50 h-50 rounded-full object-cover border-1 border-gray-300">
              </div>
              <h3 class="font-bold text-lg text-black mb-1">{{ $rwStructure['sekretaris']->warga ? $rwStructure['sekretaris']->warga->nama : $rwStructure['sekretaris']->nama }}</h3>
              <p class="text-green-600 font-medium text-sm mb-2">{{ $rwStructure['sekretaris']->jabatan }}</p>
              <p class="text-gray-500 text-sm">{{ $rwStructure['sekretaris']->periode_mulai }} - {{ $rwStructure['sekretaris']->periode_selesai }}</p>
            </div>
            @else
            <div class="bg-green-50 rounded-xl shadow-lg p-4 w-56 mr-1.5 text-center border-2 border-green-300">
              <div class="flex justify-center mb-4">
                <div class="w-20 h-20 rounded-full bg-gray-300 border-2 border-gray-400"></div>
              </div>
              <h3 class="font-bold text-lg text-black mb-1">Belum ada data</h3>
              <p class="text-green-600 font-medium text-sm mb-2">Sekretaris RW</p>
              <p class="text-gray-500 text-sm">-</p>
            </div>
            @endif

            <!-- Bendahara RW -->
            @if($rwStructure['bendahara'])
            <div class="bg-yellow-50 rounded-xl shadow-lg p-4 w-56 ml-1.5 text-center border-2 border-yellow-300">
              <div class="w-16 h-16 rounded-full mx-auto mb-4 ml-1.5 overflow-hidden bg-amber-800">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($rwStructure['bendahara']->warga ? $rwStructure['bendahara']->warga->nama : $rwStructure['bendahara']->nama) }}&background=f59e0b&color=ffffff&size=80"
                     alt="{{ $rwStructure['bendahara']->jabatan }}"
                     class="w-20 h-20 rounded-full object-cover border-2 border-gray-300">
              </div>
              <h3 class="font-bold text-lg text-black mb-1">{{ $rwStructure['bendahara']->warga ? $rwStructure['bendahara']->warga->nama : $rwStructure['bendahara']->nama }}</h3>
              <p class="text-yellow-600 font-medium text-sm mb-2">{{ $rwStructure['bendahara']->jabatan }}</p>
              <p class="text-gray-500 text-sm">{{ $rwStructure['bendahara']->periode_mulai }} - {{ $rwStructure['bendahara']->periode_selesai }}</p>
            </div>
            @else
            <div class="bg-yellow-50 rounded-xl shadow-lg p-6 w-56 text-center border-2 border-yellow-300">
              <div class="flex justify-center mb-4">
                <div class="w-20 h-20 rounded-full bg-gray-300 border-2 border-gray-400"></div>
              </div>
              <h3 class="font-bold text-lg text-black mb-1">Belum ada data</h3>
              <p class="text-yellow-600 font-medium text-sm mb-2">Bendahara RW</p>
              <p class="text-gray-500 text-sm">-</p>
            </div>
            @endif
          </div>
        </div>
      </div>
        </div>
    </section>
</x-filament-panels::page>
