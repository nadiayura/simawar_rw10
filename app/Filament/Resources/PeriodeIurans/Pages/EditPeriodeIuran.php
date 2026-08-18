<?php

namespace App\Filament\Resources\PeriodeIurans\Pages;

use App\Filament\Resources\PeriodeIurans\PeriodeIuranResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPeriodeIuran extends EditRecord
{
    protected static string $resource = PeriodeIuranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
