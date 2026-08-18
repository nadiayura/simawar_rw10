<?php

namespace App\Filament\Warga\Resources\SuratKetWargas\Pages;

use App\Filament\Warga\Resources\SuratKetWargas\SuratKetWargaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSuratKetWarga extends EditRecord
{
    protected static string $resource = SuratKetWargaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
