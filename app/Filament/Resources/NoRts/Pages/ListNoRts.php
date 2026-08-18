<?php

namespace App\Filament\Resources\NoRts\Pages;

use App\Filament\Resources\NoRts\NoRtResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNoRts extends ListRecords
{
    protected static string $resource = NoRtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
