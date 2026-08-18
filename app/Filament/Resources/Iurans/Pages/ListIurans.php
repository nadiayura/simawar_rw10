<?php

namespace App\Filament\Resources\Iurans\Pages;

use App\Filament\Resources\Iurans\IuranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIurans extends ListRecords
{
    protected static ?string $title = 'Jenis Iuran';

    protected static string $resource = IuranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Jenis Iuran'),
        ];
    }
}
