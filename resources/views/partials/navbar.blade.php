<header class="sticky top-0 z-50 border-b border-gray-200/[.06] bg-white/90 backdrop-blur-sm">
    <div class="container mx-auto flex items-center justify-between px-4 py-3 lg:px-8">
        <div class="flex items-center gap-3">
            <img src="{{ \Illuminate\Support\Facades\Storage::url('logo/logoutama.png') }}" alt="SIMAWAR 10" class="h-10 w-10">
            <div class="flex flex-col leading-tight">
                <a class="font-display text-xl font-bold text-gray-900" href="{{ route('welcome') }}">SIMAWAR 10</a>
                <span class="text-xs text-gray-600">Sistem Manajemen Warga RW 10</span>
            </div>
        </div>

        <nav class="hidden items-center gap-8 lg:flex">
            <a class="font-semibold text-gray-700 hover:text-[#133396] transition-colors" href="/kegiatan-kesehatan">Kegiatan Kesehatan</a>
            <a class="font-semibold text-gray-700 hover:text-[#133396] transition-colors" href="/galeri">Karang Taruna</a>
            <a class="font-semibold text-gray-700 hover:text-[#133396] transition-colors" href="/#struktural">Struktural</a>
        </nav>

        <div class="hidden lg:flex items-center gap-4">
            <a href="{{ url('/warga/login') }}"
               class="h-11 min-w-[100px] rounded-lg px-5 flex items-center justify-center
                      text-base font-bold text-white transition-all
                      bg-[#4c67b8] hover:bg-[#2E4EAE]">
                Masuk
            </a>
        </div>

        <div class="lg:hidden">
            <button id="mobile-menu-button"
                    class="p-2 text-gray-700 hover:text-[#133396] focus:outline-none focus:ring-2 focus:ring-[#133396] focus:ring-opacity-50 rounded-md transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden lg:hidden bg-white border-t border-gray-200">
        <div class="px-4 py-3 space-y-3">
            <a href="/kegiatan-kesehatan"
               class="block py-2 px-3 text-gray-700 hover:text-[#133396] hover:bg-gray-50 rounded-md transition-colors font-medium">
                Berita Kesehatan
            </a>
            <a href="/galeri"
               class="block py-2 px-3 text-gray-700 hover:text-[#133396] hover:bg-gray-50 rounded-md transition-colors font-medium">
                Galeri
            </a>
            <a href="/#struktural"
               class="block py-2 px-3 text-gray-700 hover:text-[#133396] hover:bg-gray-50 rounded-md transition-colors font-medium">
                Struktural
            </a>
            <div class="pt-3 border-t border-gray-200">
                <a href="{{ url('/warga/login') }}"
                   class="w-full h-11 rounded-lg px-5 text-base font-bold text-white transition-all bg-[#133396] hover:bg-[#2E4EAE] flex items-center justify-center">
                    Masuk
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (!mobileMenuButton || !mobileMenu) {
            return;
        }

        mobileMenuButton.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
        });

        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function () {
                mobileMenu.classList.add('hidden');
            });
        });
    });
</script>

