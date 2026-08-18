<?php

namespace App\Filament\Resources\Pengaduans\Pages;

use App\Filament\Resources\Pengaduans\PengaduanResource;
use App\Services\FonnteService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditPengaduan extends EditRecord
{
    protected static string $resource = PengaduanResource::class;

    protected ?string $originalStatus = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function beforeFill(): void
    {
        $this->originalStatus = $this->record->status_id;
    }

    protected function afterSave(): void
    {
        if ($this->originalStatus !== $this->record->status_id) {
            $warga = $this->record->warga;
            if ($warga && ! empty($warga->no_hp)) {
                $judul = $this->record->jdl_pengaduan;
                $tanggal = $this->record->created_at ? $this->record->created_at->format('d-m-Y') : null;
                $statusLabel = $this->record->status?->keterangan ?? '-';
                $solusi = $this->record->solusi_admin ?: null;

                $message =
                    "📢 *Update Pengaduan Anda*\n\n".
                    "Halo {$warga->nama}, terdapat pembaruan pada pengaduan Anda.\n\n".
                    "📝 *Judul:* {$judul}\n".
                    ($tanggal ? "📅 *Tanggal:* {$tanggal}\n" : '').
                    "📍 *Status Terbaru:* {$statusLabel}\n".
                    ($solusi ? "🛠️ *Solusi Admin:* {$solusi}\n\n" : "\n").
                    'Untuk detail lebih lanjut, silakan cek aplikasi. Terima kasih telah berkontribusi melalui laporan Anda 🙏';

                $response = app(FonnteService::class)->sendWhatsAppMessage(
                    $warga->no_hp,
                    $message,
                    env('DEVICE_TOKEN')
                );

                if (! ($response['status'] ?? false) || (isset($response['data']['status']) && ! $response['data']['status'])) {
                    $error = $response['error'] ?? ($response['data']['reason'] ?? 'Unknown error');
                    Log::error('Gagal mengirim pesan WhatsApp setelah ubah status', ['error' => $error]);
                }
            }
        }
    }
}
