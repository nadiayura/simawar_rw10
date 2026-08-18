<?php

namespace App\Filament\Warga\Resources\DataWargaResource\Pages;

use App\Filament\Warga\Resources\DataWargaResource;
use App\Models\FonnteDevice;
use App\Models\KetuaRt;
use App\Models\NoRt;
use App\Models\User;
use App\Models\Warga;
use App\Services\FonnteService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateDataWarga extends CreateRecord
{
    protected static string $resource = DataWargaResource::class;

    protected string $view = 'filament.warga.data-warga.create-data-warga';

    public ?array $formData = [];

    public array $rtOptions = [];

    public function mount(): void
    {
        parent::mount();

        $this->rtOptions = NoRt::query()
            ->orderBy('nomor')
            ->pluck('nomor', 'no_rt_id')
            ->toArray();

        $user = Auth::user();
        if ($user) {
            $this->formData['nama'] = $this->formData['nama'] ?? $user->name;
            $this->formData['email'] = $this->formData['email'] ?? $user->email;
        }
    }

    protected function beforeCreate(): void
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Jika user adalah tamu dan sudah memiliki warga_id, redirect ke halaman edit
        if ($user && $user->role && $user->role->name === 'tamu' && $user->warga_nik) {
            // Redirect ke halaman edit dengan data yang sudah ada
            $this->redirect($this->getResource()::getUrl('edit', ['record' => $user->warga_nik]));
        }

        // Cek apakah warga_nik sudah ada di database user
        $rawNik = $this->data['warga_nik'] ?? null;
        $prefixedNik = $rawNik ? ('WRG-'.$rawNik) : null;
        if ($prefixedNik) {
            $existingUser = \App\Models\User::where('warga_nik', $prefixedNik)->first();
            if ($existingUser) {
                // Jika email sudah ada, tampilkan notifikasi dan batalkan pembuatan data
                Notification::make()
                    ->title('NIK sudah tercatat dalam database')
                    ->body('Data dengan NIK ini sudah terdaftar. Silakan periksa kembali.')
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }
    }

    public function submitDataDiri(): void
    {
        $user = Auth::user();
        $hadWargaNik = $user && $user->warga_nik;

        if ($user && $user->role && $user->role->name === 'tamu' && $user->warga_nik) {
            $this->redirect($this->getResource()::getUrl('edit', ['record' => $user->warga_nik]));

            return;
        }

        $validated = $this->validate([
            'formData.warga_nik' => ['required', 'string', 'max:16'],
            'formData.nama' => ['required', 'string', 'max:255'],
            'formData.jenis_kelamin' => ['required', 'string', 'in:L,P'],
            'formData.agama' => ['required', 'string', 'max:50'],
            'formData.status_tinggal' => ['required', 'string', 'max:50'],
            'formData.alamat' => ['required', 'string', 'max:255'],
            'formData.no_rt_id' => ['required', 'string', 'exists:no_rts,no_rt_id'],
            'formData.no_hp' => ['required', 'string', 'max:15'],
            'formData.email' => ['required', 'email', 'max:255'],
        ]);

        $data = $validated['formData'] ?? [];

        $rawNik = $data['warga_nik'] ?? null;
        $prefixedNik = $rawNik ? ('WRG-'.$rawNik) : null;

        if ($prefixedNik) {
            $existingUser = User::where('warga_nik', $prefixedNik)->first();
            if ($existingUser) {
                Notification::make()
                    ->title('NIK sudah tercatat dalam database')
                    ->body('Data dengan NIK ini sudah terdaftar. Silakan periksa kembali.')
                    ->danger()
                    ->persistent()
                    ->send();

                return;
            }
        }

        $warga = new Warga;
        $warga->warga_nik = $prefixedNik;
        $warga->nama = $data['nama'] ?? null;
        $warga->jenis_kelamin = $data['jenis_kelamin'] ?? null;
        $warga->agama = $data['agama'] ?? null;
        $warga->status_tinggal = $data['status_tinggal'] ?? null;
        $warga->alamat = $data['alamat'] ?? null;
        $warga->no_rt_id = $data['no_rt_id'] ?? null;
        $warga->no_hp = $data['no_hp'] ?? null;
        $warga->email = $data['email'] ?? null;
        $warga->save();

        $this->record = $warga;

        if ($user) {
            if ($warga->nama) {
                $user->name = $warga->nama;
            }

            if (! $user->warga_nik) {
                $user->warga_nik = $warga->warga_nik;
            }

            $user->save();

            if ($user->role && $user->role->name === 'tamu' && ! $hadWargaNik) {
                Notification::make()
                    ->title('Data warga berhasil dibuat dan dikaitkan dengan akun Anda')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Data warga berhasil dibuat')
                    ->success()
                    ->send();
            }
        } else {
            Notification::make()
                ->title('Data warga berhasil dibuat')
                ->success()
                ->send();
        }

        $this->sendKetuaRtWhatsApp($warga);

        $this->redirect($this->getResource()::getUrl('view', ['record' => $warga->warga_nik]));
    }

    protected function afterCreate(): void
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Jika user adalah tamu dan belum memiliki warga_id
        if ($user && $user->role && $user->role->name === 'tamu' && ! $user->warga_nik) {
            // Update user dengan warga_id yang baru dibuat
            $user->warga_nik = $this->record->warga_nik;
            $user->save();

            Notification::make()
                ->title('Data warga berhasil dibuat dan dikaitkan dengan akun Anda')
                ->success()
                ->send();

        }

        if ($this->record instanceof Warga) {
            $this->sendKetuaRtWhatsApp($this->record);
        }
    }

    protected function getRedirectUrl(): string
    {
        $user = Auth::user();

        // Redirect ke halaman view setelah create
        if ($this->record) {
            return $this->getResource()::getUrl('view', ['record' => $this->record->warga_nik]);
        }

        // Jika user adalah tamu dan memiliki warga_id, redirect ke halaman view
        if ($user && $user->role && $user->role->name === 'tamu' && $user->warga_nik) {
            return $this->getResource()::getUrl('view', ['record' => $user->warga_nik]);
        }

        return $this->getResource()::getIndexUrl();
    }

    private function sendKetuaRtWhatsApp(Warga $warga): void
    {
        $rtId = $warga->no_rt_id;
        if (! $rtId) {
            Log::warning('WA data warga skipped: warga no_rt_id missing', ['id' => $warga->getKey()]);
            Notification::make()->title('Notifikasi Ketua RT tidak dikirim')->body('RT warga belum terisi.')->warning()->send();

            return;
        }

        $hp = KetuaRt::query()->ketuaRt()->active()->where('no_rt_id', $rtId)->value('no_hp');
        if (! $hp) {
            $hp = KetuaRt::query()
                ->ketuaRt()
                ->where('no_rt_id', $rtId)
                ->orderByDesc('periode_mulai')
                ->value('no_hp');
        }
        if (! $hp) {
            Log::warning('WA data warga skipped: Ketua RT no_hp missing', ['rt_id' => $rtId, 'id' => $warga->getKey()]);
            Notification::make()->title('Notifikasi Ketua RT tidak dikirim')->body('Nomor HP Ketua RT belum tersedia.')->warning()->send();

            return;
        }

        $deviceToken = FonnteDevice::query()
            ->whereIn('status', ['connected', 'connect'])
            ->orderByDesc('last_synced_at')
            ->value('token');
        if (! $deviceToken) {
            $deviceToken = FonnteDevice::query()
                ->whereIn('status', ['connected', 'connect'])
                ->latest('updated_at')
                ->value('token');
        }
        if (! $deviceToken) {
            Log::warning('WA data warga skipped: no connected device', ['id' => $warga->getKey()]);
            Notification::make()->title('Notifikasi Ketua RT tidak dikirim')->body('Perangkat WhatsApp belum terhubung.')->warning()->send();

            return;
        }

        $target = $this->normalizePhone((string) $hp);
        if ($target === '') {
            Log::warning('WA data warga skipped: invalid phone after normalize', ['hp' => $hp, 'id' => $warga->getKey()]);
            Notification::make()->title('Notifikasi Ketua RT tidak dikirim')->body('Nomor HP Ketua RT tidak valid.')->warning()->send();

            return;
        }

        $rtNomor = NoRt::find($rtId)?->nomor ?? $rtId;
        $tanggal = $warga->created_at ? $warga->created_at->format('d-m-Y') : now()->format('d-m-Y');
        $message =
            '📢 *Data warga baru untuk diverifikasi*'."\n\n".
            'Halo Ketua RT '.($rtNomor ?: '').','.
            "\n\n".
            "\n Terdapat pendaftaran data warga baru"."\n".
            ($warga->nama ? 'Nama :'.$warga->nama."\n" : '').
            ($rtNomor ? ' RT :'.$rtNomor."\n" : '').
            ($tanggal ? ' Tanggal :'.$tanggal."\n" : '').
            'Silakan cek aplikasi untuk detail data warga.';

        $resp = app(FonnteService::class)->sendWhatsAppMessage($target, $message, $deviceToken);
        if (! ($resp['status'] ?? false) || (isset($resp['data']['status']) && ! $resp['data']['status'])) {
            $err = $resp['error'] ?? ($resp['data']['reason'] ?? 'Error');
            Log::error('WA notify data warga gagal', ['id' => $warga->getKey(), 'reason' => $err]);
            Notification::make()->title('Gagal kirim WhatsApp ke Ketua RT')->body($err)->danger()->send();
        } else {
            Log::info('WA notify data warga terkirim', ['id' => $warga->getKey(), 'target' => $target]);
            $masked = preg_replace('/\d(?=\d{4})/', '•', $target);
            Notification::make()->title('Pesan WA Ketua RT terkirim')->body('Terkirim ke '.$masked)->success()->send();
        }
    }

    private function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === null || $digits === '') {
            return '';
        }
        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }
        if (str_starts_with($digits, '62')) {
            return $digits;
        }
        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }
}
