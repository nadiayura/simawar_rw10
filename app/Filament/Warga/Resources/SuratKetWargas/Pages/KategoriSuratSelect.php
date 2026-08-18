<?php

namespace App\Filament\Warga\Resources\SuratKetWargas\Pages;

use App\Filament\Warga\Resources\SuratKetWargas\SuratKetWargaResource;
use App\Models\JenisSurat;
use Filament\Resources\Pages\Page;

class KategoriSuratSelect extends Page
{
    protected static string $resource = SuratKetWargaResource::class;

    protected static ?string $title = 'Pengajuan Surat';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.warga.surat_ket_wargas.kategori-select';

    public function getBreadcrumbs(): array
    {
        return [
            SuratKetWargaResource::getUrl('index') => 'Pengajuan Surat',
        ];
    }

    public function getViewData(): array
    {
        return [
            'jenis' => JenisSurat::query()
                ->where('is_active', true)
                ->orderBy('nama_surat')
                ->get(['jenis_surat_id', 'nama_surat', 'deskripsi'])
                ->toArray(),
            'createUrl' => route('filament.warga.resources.surat-ket-wargas.create'),
            'listUrl' => route('filament.warga.resources.surat-ket-wargas.list'),
        ];
    }
}
