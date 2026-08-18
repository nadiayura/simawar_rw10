<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($records as $record)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <!-- Image -->
                <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                    @if($record->foto_kegiatan)
                        <img src="{{ asset('storage/' . $record->foto_kegiatan) }}" 
                             alt="{{ $record->nama_kegiatan }}" 
                             class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-300 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                
                <!-- Content -->
                <div class="p-4">
                    <h3 class="font-semibold text-lg text-gray-900 mb-2 line-clamp-2">
                        {{ $record->nama_kegiatan }}
                    </h3>
                    
                    @if($record->deskripsi)
                        <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                            {{ $record->deskripsi }}
                        </p>
                    @endif
                    
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($record->tanggal)->format('d M Y') }}
                        </span>
                        
                        <!-- Action Buttons -->
                        <div class="flex space-x-2">
                            <a href="{{ route('filament.admin.resources.galeri-katar.view', $record) }}" 
                               class="inline-flex items-center px-3 py-1 bg-[#2ea7d6] text-white text-xs font-medium rounded-md hover:bg-[#2ea7d6] transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View
                            </a>
                            
                            <a href="{{ route('filament.admin.resources.galeri-katar.edit', $record) }}" 
                               class="inline-flex items-center px-3 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded-md hover:bg-amber-200 transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    @if($records->isEmpty())
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada galeri</h3>
            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat galeri kegiatan baru.</p>
        </div>
    @endif
    
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</x-filament-panels::page>
