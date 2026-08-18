<?php

namespace App\Filament\Warga\Resources\DataWargaResource\Pages;

use App\Filament\Warga\Resources\DataWargaResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditDataWarga extends EditRecord
{
    protected static string $resource = DataWargaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak perlu menampilkan action delete
        ];
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Data warga berhasil diperbarui')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        // Untuk user tamu, tetap di halaman edit setelah menyimpan
        $user = Auth::user();
        if ($user && $user->role && $user->role->name === 'tamu') {
            return $this->getResource()::getUrl('edit', ['record' => $this->record->warga_nik]);
        }

        return $this->getResource()::getUrl('view', ['record' => $this->record->warga_nik]);
    }

    // Menonaktifkan form secara default, harus klik tombol edit dulu
    protected function isFormDisabled(): bool
    {
        // Cek apakah ada parameter edit=true di URL
        return ! request()->has('edit');
    }

    // Menambahkan tombol edit untuk mengaktifkan form
    protected function getActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Edit Data')
                ->url(fn () => $this->getResource()::getUrl('edit', ['record' => $this->record->warga_nik, 'edit' => 'true']))
                ->visible(fn () => ! request()->has('edit'))
                ->color('primary'),
        ];
    }
}
