<?php

namespace App\Filament\Warga\Resources\Pengaduans\Pages;

use App\Filament\Warga\Resources\Pengaduans\PengaduanResource;
use App\Models\FonnteDevice;
use App\Models\KetuaRt;
use App\Models\NoRt;
use App\Services\FonnteService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreatePengaduan extends CreateRecord
{
    protected static string $resource = PengaduanResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        $warga = $record?->warga;
        $rtId = $warga?->no_rt_id;
        if (! $rtId) {
            Log::warning('WA pengaduan skipped: warga no_rt_id missing', ['id' => $record?->getKey()]);
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
            Log::warning('WA pengaduan skipped: Ketua RT no_hp missing', ['rt_id' => $rtId, 'id' => $record->getKey()]);
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
            Log::warning('WA pengaduan skipped: no connected device', ['id' => $record->getKey()]);
            Notification::make()->title('Notifikasi Ketua RT tidak dikirim')->body('Perangkat WhatsApp belum terhubung.')->warning()->send();

            return;
        }
        $target = $this->normalizePhone((string) $hp);
        if ($target === '') {
            Log::warning('WA pengaduan skipped: invalid phone after normalize', ['hp' => $hp, 'id' => $record->getKey()]);
            Notification::make()->title('Notifikasi Ketua RT tidak dikirim')->body('Nomor HP Ketua RT tidak valid.')->warning()->send();

            return;
        }
        $jenis = $record->jenisPengaduan?->nama;
        $judul = $record->jdl_pengaduan;
        $tanggal = $record->tgl_pengajuan ? $record->tgl_pengajuan->format('d-m-Y') : now()->format('d-m-Y');
        $rtNomor = NoRt::find($rtId)?->nomor ?? $rtId;
        $message =
            'Halo Ketua RT '.($rtNomor ?: '').','.
            "\n\n".
            '📢 *Pengaduan baru warga*'.
            "\n Terdapat pengaduan baru dari warga".
            ($warga?->nama ? ' Nama :'.$warga->nama."\n" : '').
            ($rtNomor ? ' RT :'.$rtNomor."\n" : '').
            ($tanggal ? ' Tanggal :'.$tanggal."\n" : '').
            ($jenis ? ' Jenis :'.$jenis."\n" : '').
            ($judul ? ' Judul :'.$judul."\n" : '').
            'Silakan cek aplikasi untuk detail pengaduan.';
        $resp = app(FonnteService::class)->sendWhatsAppMessage($target, $message, $deviceToken);
        if (! ($resp['status'] ?? false) || (isset($resp['data']['status']) && ! $resp['data']['status'])) {
            $err = $resp['error'] ?? ($resp['data']['reason'] ?? 'Error');
            Log::error('WA notify pengaduan gagal', ['id' => $record->getKey(), 'reason' => $err]);
            Notification::make()->title('Gagal kirim WhatsApp ke Ketua RT')->body($err)->danger()->send();
        } else {
            Log::info('WA notify pengaduan terkirim', ['id' => $record->getKey(), 'target' => $target]);
            $masked = preg_replace('/\\d(?=\\d{4})/', '•', $target);
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
