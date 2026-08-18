<?php

namespace App\Filament\Warga\Resources\SuratKetWargas\Pages;

use App\Filament\Warga\Resources\SuratKetWargas\SuratKetWargaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuratKetWargas extends ListRecords
{
    protected static string $resource = SuratKetWargaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajukan Surat'),
        ];
    }
}
