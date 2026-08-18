<?php

namespace App\Filament\Resources\NoRts\Pages;

use App\Filament\Resources\NoRts\NoRtResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNoRt extends EditRecord
{
    protected static string $resource = NoRtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
