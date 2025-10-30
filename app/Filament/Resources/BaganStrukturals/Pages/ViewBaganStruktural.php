<?php

namespace App\Filament\Resources\BaganStrukturals\Pages;

use App\Filament\Resources\BaganStrukturals\BaganStrukturalResource;
use App\Models\Struktural;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        if (!$user || (!$user->role->isRT() && !$user->role->isRW() && !$user->role->isAdmin())) {
            abort(403, 'Unauthorized access');
        }
    }

    protected function getViewData(): array
    {
        // Access structural data without tenant filtering since this represents
        // the complete organizational structure that should be visible to all users
        $structuralQuery = Struktural::withoutGlobalScopes()->with('warga')->active();

        // Get RW structure data
        $rwStructure = [
            'ketua' => $structuralQuery->clone()->where('jabatan', 'LIKE', '%Ketua RW%')->with('warga')->first(),
            'sekretaris' => $structuralQuery->clone()->where('jabatan', 'LIKE', '%Sekretaris RW%')->with('warga')->first(),
            'bendahara' => $structuralQuery->clone()->where('jabatan', 'LIKE', '%Bendahara RW%')->with('warga')->first(),
        ];

        // Get RT structures data grouped by RT number
        $rtStructures = [];
        $rtNumbers = ['001', '002', '003', '004', '005', '006'];

        foreach ($rtNumbers as $rtNumber) {
            $rtStructures[$rtNumber] = [
                'ketua' => $structuralQuery->clone()->ketuaRt()->where('no_rt', $rtNumber)->first(),
                'sekretaris' => $structuralQuery->clone()
                    ->where('jabatan', 'LIKE', '%Sekretaris RT%')
                    ->where('no_rt', $rtNumber)
                    ->first(),
                'bendahara' => $structuralQuery->clone()
                    ->where('jabatan', 'LIKE', '%Bendahara RT%')
                    ->where('no_rt', $rtNumber)
                    ->first(),
            ];
        }

        return [
            'rwStructure' => $rwStructure,
            'rtStructures' => $rtStructures,
        ];
    }
}
