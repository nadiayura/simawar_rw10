<?php

namespace App\Filament\Resources\Kegiatans\Pages;

use App\Filament\Resources\Kegiatans\KegiatanResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateKegiatan extends CreateRecord
{
    protected static string $resource = KegiatanResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan tenant_id diset berdasarkan tenant yang sedang login
        $tenant = Filament::getTenant();
        if ($tenant) {
            $data['tenant_id'] = $tenant->id;
        }
        
        return $data;
    }
}
