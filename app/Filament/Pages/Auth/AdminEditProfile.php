<?php

namespace App\Filament\Pages\Auth;

use App\Models\Warga;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminEditProfile extends BaseEditProfile
{
    protected string $view = 'filament.pages.admin-edit-profile';

    public ?array $wargaData = [];

    public ?array $accountData = [];

    public function mount(): void
    {
        parent::mount();

        $user = Filament::auth()->user();

        if ($user) {
            $this->accountData = [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => '',
                'new_password' => '',
                'new_password_confirmation' => '',
            ];
        }

        if ($user && $user->warga_nik) {
            $warga = Warga::find($user->warga_nik);

            if ($warga) {
                $this->wargaData = [
                    'nama' => $warga->nama,
                    'email' => $warga->email,
                    'no_hp' => $warga->no_hp,
                    'jenis_kelamin' => $warga->jenis_kelamin,
                    'agama' => $warga->agama,
                    'status_tinggal' => $warga->status_tinggal,
                    'alamat' => $warga->alamat,
                ];
            }
        }
    }

    public static function isSimple(): bool
    {
        return false;
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Profil Pengguna';
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.dashboard') => 'Dashboard',
            URL::current() => 'Profil Pengguna',
        ];
    }

    public function updateWarga(): void
    {
        try {
            $user = Filament::auth()->user();

            if (! $user || ! $user->warga_nik) {
                return;
            }

            $this->validate([
                'wargaData.nama' => ['required', 'string', 'max:255'],
                'wargaData.email' => ['nullable', 'email', 'max:255'],
                'wargaData.no_hp' => ['nullable', 'string', 'max:20'],
                'wargaData.jenis_kelamin' => ['nullable', 'string', 'max:20'],
                'wargaData.agama' => ['nullable', 'string', 'max:50'],
                'wargaData.status_tinggal' => ['nullable', 'string', 'max:50'],
                'wargaData.alamat' => ['nullable', 'string'],
            ]);

            $warga = Warga::find($user->warga_nik);

            if (! $warga) {
                return;
            }

            $warga->nama = $this->wargaData['nama'] ?? null;
            $warga->email = $this->wargaData['email'] ?? null;
            $warga->no_hp = $this->wargaData['no_hp'] ?? null;
            $warga->jenis_kelamin = $this->wargaData['jenis_kelamin'] ?? null;
            $warga->agama = $this->wargaData['agama'] ?? null;
            $warga->status_tinggal = $this->wargaData['status_tinggal'] ?? null;
            $warga->alamat = $this->wargaData['alamat'] ?? null;
            $warga->save();

            Notification::make()
                ->title('Data warga berhasil diperbarui')
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Data warga gagal diperbarui')
                ->body('Terjadi kesalahan saat menyimpan data warga. Silakan coba lagi.')
                ->danger()
                ->send();
        }
    }

    public function updateAccount(): void
    {
        try {
            $user = Filament::auth()->user();

            if (! $user) {
                return;
            }

            $shouldUpdatePassword = ! empty($this->accountData['current_password'])
                || ! empty($this->accountData['new_password'])
                || ! empty($this->accountData['new_password_confirmation']);

            $rules = [
                'accountData.name' => ['required', 'string', 'max:255'],
                'accountData.email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            ];

            if ($shouldUpdatePassword) {
                $rules['accountData.current_password'] = ['required', 'current_password'];
                $rules['accountData.new_password'] = ['required', 'confirmed', Password::defaults()];
                $rules['accountData.new_password_confirmation'] = ['required'];
            }

            $this->validate($rules);

            $user->name = $this->accountData['name'] ?? $user->name;
            $user->email = $this->accountData['email'] ?? $user->email;

            $passwordChanged = false;

            if ($shouldUpdatePassword) {
                $user->password = Hash::make((string) $this->accountData['new_password']);
                $passwordChanged = true;
            }

            $user->save();

            if ($user->warga_nik) {
                $warga = Warga::find($user->warga_nik);

                if ($warga) {
                    $warga->email = $this->accountData['email'] ?? $warga->email;
                    $warga->save();
                }
            }

            if ($passwordChanged) {
                Notification::make()
                    ->title('Kata sandi berhasil diubah, silakan masuk kembali')
                    ->success()
                    ->send();

                Filament::auth()->logout();

                $this->redirect(Filament::getLoginUrl());

                return;
            }

            Notification::make()
                ->title('Data berhasil diperbarui')
                ->success()
                ->seconds(3)
                ->send();

            $this->dispatch('refresh-profile-after-success');
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            if (isset($errors['accountData.current_password'])) {
                Notification::make()
                    ->title('Kata sandi saat ini tidak sesuai')
                    ->danger()
                    ->send();
            } else {
                Notification::make()
                    ->title('Data gagal diperbarui')
                    ->body('Periksa kembali isian formulir Anda.')
                    ->danger()
                    ->send();
            }

            throw $exception;
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Data gagal diperbarui')
                ->body('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.')
                ->danger()
                ->send();
        }
    }

    public function updateProfile(): void
    {
        // Profil bawaan tidak digunakan, seluruh logika akun ditangani oleh updateAccount().
    }
}
