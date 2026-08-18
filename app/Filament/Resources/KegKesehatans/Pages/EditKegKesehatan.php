<?php

namespace App\Filament\Resources\KegKesehatans\Pages;

use App\Filament\Resources\KegKesehatans\KegKesehatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKegKesehatan extends EditRecord
{
    protected static string $resource = KegKesehatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('Hapus Kegiatan Kesehatan')
                ->modalDescription('Apakah Anda yakin ingin menghapus kegiatan kesehatan ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Hapus')
                ->modalCancelActionLabel('Batal'),
        ];
    }
}
