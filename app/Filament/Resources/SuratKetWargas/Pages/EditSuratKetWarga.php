<?php

namespace App\Filament\Resources\SuratKetWargas\Pages;

use App\Filament\Resources\SuratKetWargas\SuratKetWargaResource;
use App\Models\FonnteDevice;
use App\Models\KetuaRt;
use App\Services\FonnteService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditSuratKetWarga extends EditRecord
{
    protected static string $resource = SuratKetWargaResource::class;

    protected ?string $originalStatusId = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function beforeFill(): void
    {
        $this->originalStatusId = $this->record->status_id;
    }

    protected function afterSave(): void
    {
        Notification::make()->title('Status surat diperbarui')->success()->send();
        if ($this->originalStatusId !== $this->record->status_id) {
            $warga = $this->record->warga;
            if ($warga && ! empty($warga->no_hp)) {
                $deviceToken = $this->resolveDeviceToken();
                if (! $deviceToken) {
                    Notification::make()->title('Gagal kirim WhatsApp')->body('Perangkat WhatsApp belum terhubung')->danger()->send();

                    return;
                }
                $jenis = $this->record->jenisSurat?->nama_surat;
                $tanggalPengajuan = $this->record->tgl_pengajuan ? $this->record->tgl_pengajuan->format('d-m-Y') : null;
                $statusLabel = $this->record->status?->keterangan ?? '-';
                $tglSelesai = $this->record->tgl_selesai ? $this->record->tgl_selesai->format('d-m-Y') : null;

                $message =
                    "📢 *Update Surat Keterangan Anda*\n\n".
                    "Halo {$warga->nama}, terdapat pembaruan pada pengajuan surat Anda.\n\n".
                    (isset($jenis) ? "📝 *Jenis Surat:* {$jenis}\n" : '').
                    ($tanggalPengajuan ? "📅 *Tanggal Pengajuan:* {$tanggalPengajuan}\n" : '').
                    "📍 *Status Terbaru:* {$statusLabel}\n".
                    ($tglSelesai ? "✅ *Tanggal Selesai:* {$tglSelesai}\n" : '').
                    "\nSilakan cek aplikasi untuk detail lebih lanjut. Terima kasih 🙏";

                $target = $this->normalizePhone((string) $warga->no_hp);
                if ($target === '') {
                    Notification::make()->title('Gagal kirim WhatsApp')->body('Format nomor HP warga tidak valid')->danger()->send();

                    return;
                }

                $response = app(FonnteService::class)->sendWhatsAppMessage(
                    $target,
                    $message,
                    $deviceToken
                );

                if (! ($response['status'] ?? false) || (isset($response['data']['status']) && ! $response['data']['status'])) {
                    $error = $response['error'] ?? ($response['data']['reason'] ?? 'Unknown error');
                    Log::error('Gagal mengirim pesan WhatsApp setelah ubah status surat', ['error' => $error]);
                    Notification::make()->title('Gagal kirim WhatsApp')->body($error)->danger()->send();
                } else {
                    Notification::make()->title('Notifikasi WhatsApp terkirim')->success()->send();
                }
            }
        }
    }

    private function resolveDeviceToken(): ?string
    {
        $rtId = $this->record?->warga?->no_rt_id;
        if ($rtId) {
            $ketua = KetuaRt::query()->ketuaRt()->active()->where('no_rt_id', $rtId)->first();
            $hp = $ketua?->no_hp;
            if ($hp) {
                $targetDigits = preg_replace('/\D+/', '', (string) $hp);
                $devices = FonnteDevice::query()
                    ->whereIn('status', ['connected', 'connect'])
                    ->orderByDesc('last_synced_at')
                    ->get(['device', 'token']);
                foreach ($devices as $d) {
                    $devDigits = preg_replace('/\D+/', '', (string) $d->device);
                    if ($devDigits !== '' && ($devDigits === $targetDigits || str_ends_with($devDigits, substr($targetDigits, -10)))) {
                        return $d->token;
                    }
                }
            }
        }
        $active = FonnteDevice::query()->whereIn('status', ['connected', 'connect'])->orderByDesc('last_synced_at')->value('token');

        return $active ?: env('DEVICE_TOKEN');
    }

    private function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === null) {
            return '';
        }

        if ($digits === '') {
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
