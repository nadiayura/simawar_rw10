<?php

namespace App\Filament\Resources\Strukturals\Pages;

use App\Filament\Resources\Strukturals\StrukturalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStrukturals extends ListRecords
{
    protected static string $resource = StrukturalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
