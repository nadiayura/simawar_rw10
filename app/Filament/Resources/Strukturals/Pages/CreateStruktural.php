<?php

namespace App\Filament\Resources\Strukturals\Pages;

use App\Filament\Resources\Strukturals\StrukturalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStruktural extends CreateRecord
{
    protected static string $resource = StrukturalResource::class;

    protected static ?string $title = 'Create Struktural RW';
}
