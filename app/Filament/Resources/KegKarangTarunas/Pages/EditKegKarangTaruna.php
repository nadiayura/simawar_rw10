<?php

namespace App\Filament\Resources\KegKarangTarunas\Pages;

use App\Filament\Resources\KegKarangTarunas\KegKarangTarunaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKegKarangTaruna extends EditRecord
{
    protected static string $resource = KegKarangTarunaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('Hapus Kegiatan Karang Taruna')
                ->modalDescription('Apakah Anda yakin ingin menghapus kegiatan ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Hapus')
                ->modalCancelActionLabel('Batal'),
        ];
    }
}
