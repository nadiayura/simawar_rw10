<?php

namespace App\Filament\Resources\KetuaRts\Pages;

use App\Filament\Resources\KetuaRts\KetuaRtResource;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Auth\Access\AuthorizationException;

class EditKetuaRt extends EditRecord
{
    protected static string $resource = KetuaRtResource::class;

    // Simpan id_warga awal sebelum edit
    protected ?string $originalWargaNik = null;

    public function mount(int|string $record): void
    {
        // Hapus blok yang melempar AuthorizationException untuk RT
        // Check authorization before mounting the page

        parent::mount($record);
        $this->originalWargaNik = $this->record->warga_nik;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->originalWargaNik = $this->record->warga_nik;

        return $data;
    }

    protected function afterSave(): void
    {
        // Sinkronkan role ketika pemegang jabatan RT berubah
        $this->syncRolesForRtPositionChange();
    }

    protected function syncRolesForRtPositionChange(): void
    {
        $previousWargaNik = $this->originalWargaNik;
        $currentWargaNik = $this->record->warga_nik;

        if (! $currentWargaNik || $currentWargaNik === $previousWargaNik) {
            return;
        }

        $jabatan = strtolower($this->record->jabatan ?? '');
        $roleName = null;
        if (str_contains($jabatan, 'ketua rt')) {
            $roleName = 'rt';
        } elseif (str_contains($jabatan, 'sekretaris rt')) {
            $roleName = 'skre_rt';
        } elseif (str_contains($jabatan, 'bendahara rt')) {
            $roleName = 'benda_rt';
        }

        $targetRole = $roleName
            ? \App\Models\Role::where('name', $roleName)->first()
            : \App\Models\Role::where('level', 'rt_admin')->first(); // fallback
        $wargaRole = \App\Models\Role::where('name', 'warga')->first();

        \Illuminate\Support\Facades\DB::transaction(function () use ($currentWargaNik, $previousWargaNik, $targetRole, $wargaRole) {
            // Promosi: tetapkan role user pemegang jabatan yang baru (jika ada)
            if ($targetRole) {
                $newUser = \App\Models\User::where('warga_nik', $currentWargaNik)->first();
                if ($newUser) {
                    $newUser->role_id = $targetRole->id;
                    $newUser->save();
                }
            }

            // Demosi: turunkan role user pemegang jabatan lama menjadi warga (selalu, tanpa cek is_active)
            if ($previousWargaNik && $wargaRole) {
                $prevUser = \App\Models\User::where('warga_nik', $previousWargaNik)->first();
                if ($prevUser) {
                    $prevUser->role_id = $wargaRole->id;
                    $prevUser->save();
                }
            }
        });

        // Jika user yang sedang login adalah pemegang jabatan lama, tampilkan notifikasi dan logout ke panel warga
        $user = request()->user();
        if ($user && $previousWargaNik && $user->warga_nik === $previousWargaNik) {
            Notification::make()
                ->title('Akses admin Anda telah dialihkan')
                ->body('Perubahan jabatan membuat Anda menjadi warga. Anda akan logout, silakan login di panel warga.')
                ->warning()
                ->persistent()
                ->send();

            $user->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            $this->redirect('/warga/login');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => KetuaRtResource::canDelete($this->record)),
        ];
    }
}
