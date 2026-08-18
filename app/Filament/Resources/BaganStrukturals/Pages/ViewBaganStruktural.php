<?php

namespace App\Filament\Resources\BaganStrukturals\Pages;

use App\Filament\Resources\BaganStrukturals\BaganStrukturalResource;
use App\Models\NoRt;
use App\Models\Struktural;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ViewBaganStruktural extends Page
{
    protected static string $resource = BaganStrukturalResource::class;

    public function getView(): string
    {
        return 'filament.resources.bagan-strukturals.pages.view-bagan-struktural';
    }

    protected static ?string $title = 'Bagan Struktural RW & RT';

    public function mount(): void
    {
        // Check if user has permission to view this page
        $user = Auth::user();
        if (! $user || (! $user->role->isRT() && ! $user->role->isRW() && ! $user->role->isAdmin())) {
            abort(403, 'Unauthorized access');
        }
    }

    protected function getViewData(): array
    {
        $structuralBase = Struktural::withoutGlobalScopes()->with('warga');

        $rwStructure = [
            'ketua' => $structuralBase->clone()->active()->where('jabatan', 'LIKE', '%Ketua RW%')->first()
                ?: $structuralBase->clone()->where('jabatan', 'LIKE', '%Ketua RW%')->first(),
            'sekretaris' => $structuralBase->clone()->active()->where('jabatan', 'LIKE', '%Sekretaris RW%')->first()
                ?: $structuralBase->clone()->where('jabatan', 'LIKE', '%Sekretaris RW%')->first(),
            'bendahara' => $structuralBase->clone()->active()->where('jabatan', 'LIKE', '%Bendahara RW%')->first()
                ?: $structuralBase->clone()->where('jabatan', 'LIKE', '%Bendahara RW%')->first(),
        ];

        $rtStructures = [];
        $rtMasters = NoRt::query()->orderBy('nomor')->get(['no_rt_id', 'nomor']);

        foreach ($rtMasters as $rt) {
            $rtId = $rt->no_rt_id;
            $rtNumber = str_pad((string) $rt->nomor, 2, '0', STR_PAD_LEFT);

            $rtStructures[$rtNumber] = [
                'ketua' => $structuralBase->clone()->active()
                    ->ketuaRt()
                    ->whereHas('warga', fn ($q) => $q->where('no_rt_id', $rtId))
                    ->first()
                    ?: $structuralBase->clone()->ketuaRt()
                        ->whereHas('warga', fn ($q) => $q->where('no_rt_id', $rtId))
                        ->first(),
                'sekretaris' => $structuralBase->clone()->active()
                    ->where('jabatan', 'LIKE', '%Sekretaris RT%')
                    ->whereHas('warga', fn ($q) => $q->where('no_rt_id', $rtId))
                    ->first()
                    ?: $structuralBase->clone()
                        ->where('jabatan', 'LIKE', '%Sekretaris RT%')
                        ->whereHas('warga', fn ($q) => $q->where('no_rt_id', $rtId))
                        ->first(),
                'bendahara' => $structuralBase->clone()->active()
                    ->where('jabatan', 'LIKE', '%Bendahara RT%')
                    ->whereHas('warga', fn ($q) => $q->where('no_rt_id', $rtId))
                    ->first()
                    ?: $structuralBase->clone()
                        ->where('jabatan', 'LIKE', '%Bendahara RT%')
                        ->whereHas('warga', fn ($q) => $q->where('no_rt_id', $rtId))
                        ->first(),
            ];
        }

        return [
            'rwStructure' => $rwStructure,
            'rtStructures' => $rtStructures,
        ];
    }
}
