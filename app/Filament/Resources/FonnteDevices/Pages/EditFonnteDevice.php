<?php

namespace App\Filament\Resources\FonnteDevices\Pages;

use App\Filament\Resources\FonnteDevices\FonnteDeviceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFonnteDevice extends EditRecord
{
    protected static string $resource = FonnteDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
