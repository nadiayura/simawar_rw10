<?php

namespace App\Filament\Resources\Strukturals\Pages;

use App\Filament\Resources\Strukturals\StrukturalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStruktural extends EditRecord
{
    protected static string $resource = StrukturalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
