<?php

namespace App\Filament\Warga\Resources\DataWargaResource\Pages;

use App\Filament\Warga\Resources\DataWargaResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Validation\ValidationException;

class ViewDataWarga extends ViewRecord
{
    protected static string $resource = DataWargaResource::class;

    protected string $view = 'filament.warga.data-warga.view-data-warga';

    public ?array $formData = [];

    public function mount($record): void
    {
        parent::mount($record);

        $this->formData = [
            'warga_nik' => $this->record?->warga_nik,
            'nama' => $this->record?->nama,
            'jenis_kelamin' => $this->record?->jenis_kelamin,
            'agama' => $this->record?->agama,
            'status_tinggal' => $this->record?->status_tinggal,
            'alamat' => $this->record?->alamat,
            'no_hp' => $this->record?->no_hp,
            'email' => $this->record?->email,
        ];
    }

    public function updateDataDiri(): void
    {
        try {
            $this->validate([
                'formData.nama' => ['required', 'string', 'max:255'],
                'formData.email' => ['nullable', 'email', 'max:255'],
                'formData.no_hp' => ['nullable', 'string', 'max:20'],
                'formData.jenis_kelamin' => ['nullable', 'string', 'max:20'],
                'formData.agama' => ['nullable', 'string', 'max:50'],
                'formData.status_tinggal' => ['nullable', 'string', 'max:50'],
                'formData.alamat' => ['nullable', 'string'],
            ]);

            if (! $this->record) {
                return;
            }

            $this->record->nama = $this->formData['nama'] ?? null;
            $this->record->email = $this->formData['email'] ?? null;
            $this->record->no_hp = $this->formData['no_hp'] ?? null;
            $this->record->jenis_kelamin = $this->formData['jenis_kelamin'] ?? null;
            $this->record->agama = $this->formData['agama'] ?? null;
            $this->record->status_tinggal = $this->formData['status_tinggal'] ?? null;
            $this->record->alamat = $this->formData['alamat'] ?? null;
            $this->record->save();

            Notification::make()
                ->title('Data berhasil diperbarui')
                ->success()
                ->seconds(3)
                ->send();

            $this->dispatch('refresh-profile-after-success');
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Data gagal diperbarui')
                ->body('Periksa kembali isian formulir Anda.')
                ->danger()
                ->send();

            throw $exception;
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Data gagal diperbarui')
                ->body('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.')
                ->danger()
                ->send();
        }
    }
}
