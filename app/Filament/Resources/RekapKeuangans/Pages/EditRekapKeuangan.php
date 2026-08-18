<?php

namespace App\Filament\Resources\RekapKeuangans\Pages;

use App\Filament\Resources\RekapKeuangans\RekapKeuanganResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRekapKeuangan extends EditRecord
{
    protected static string $resource = RekapKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
