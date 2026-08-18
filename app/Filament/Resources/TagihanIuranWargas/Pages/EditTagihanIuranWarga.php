<?php

namespace App\Filament\Resources\TagihanIuranWargas\Pages;

use App\Filament\Resources\TagihanIuranWargas\TagihanIuranWargaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTagihanIuranWarga extends EditRecord
{
    protected static string $resource = TagihanIuranWargaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
