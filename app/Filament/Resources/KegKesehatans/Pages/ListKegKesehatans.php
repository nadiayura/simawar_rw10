<?php

namespace App\Filament\Resources\KegKesehatans\Pages;

use App\Filament\Resources\KegKesehatans\KegKesehatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKegKesehatans extends ListRecords
{
    protected static string $resource = KegKesehatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
