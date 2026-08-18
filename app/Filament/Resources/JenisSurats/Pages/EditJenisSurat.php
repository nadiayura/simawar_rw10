<?php

namespace App\Filament\Resources\JenisSurats\Pages;

use App\Filament\Resources\JenisSurats\JenisSuratResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJenisSurat extends EditRecord
{
    protected static string $resource = JenisSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
