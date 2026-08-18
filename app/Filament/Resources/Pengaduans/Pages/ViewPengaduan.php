<?php

namespace App\Filament\Resources\Pengaduans\Pages;

use App\Filament\Resources\Pengaduans\PengaduanResource;
use App\Models\FonnteDevice;
use App\Models\KetuaRt;
use App\Models\Status;
use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;

class ViewPengaduan extends ViewRecord
{
    protected static string $resource = PengaduanResource::class;

    public function mount($record): void
    {
        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('proses_pengaduan')
                ->label('Proses Pengaduan')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->form([
                    Select::make('status_id')
                        ->label('Status')
                        ->options(fn () => Status::query()->where('fitur', 'pengaduan')->orderBy('keterangan')->pluck('keterangan', 'status_id')->toArray())
                        ->default(fn () => $this->record->status_id)
                        ->required()
                        ->live(),
                    Textarea::make('solusi_admin')
                        ->label('Solusi Admin')
                        ->default(fn () => $this->record->solusi_admin)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $originalStatusId = $this->record->status_id;
                    $originalSolusiAdmin = $this->record->solusi_admin;

                    $this->record->status_id = $data['status_id'] ?? $this->record->status_id;
                    if (array_key_exists('solusi_admin', $data)) {
                        $this->record->solusi_admin = $data['solusi_admin'];
                    }
                    $this->record->save();

                    Notification::make()->title('Status pengaduan diperbarui')->success()->send();

                    $statusChanged = $originalStatusId !== $this->record->status_id;
                    $solusiChanged = (string) $originalSolusiAdmin !== (string) $this->record->solusi_admin;

                    if ($statusChanged || $solusiChanged) {
                        $warga = $this->record->warga;
                        if ($warga && ! empty($warga->no_hp)) {
                            $deviceToken = $this->resolveDeviceToken();
                            if (! $deviceToken) {
                                Notification::make()->title('Gagal kirim WhatsApp')->body('Perangkat WhatsApp belum terhubung')->danger()->send();

                                return;
                            }
                            $jenis = $this->record->jenisPengaduan?->nama;
                            $judul = $this->record->jdl_pengaduan;
                            $tanggalPengajuan = $this->record->tgl_pengajuan ? $this->record->tgl_pengajuan->format('d-m-Y') : null;
                            $statusLabel = $this->record->status?->keterangan ?? '-';

                            $message =
                                "📢 *Update Pengaduan Anda*\n\n".
                                "Halo {$warga->nama}, terdapat pembaruan pada pengaduan Anda.\n\n".
                                (isset($jenis) ? "📝 *Jenis Pengaduan:* {$jenis}\n" : '').
                                (isset($judul) ? "📝 *Judul:* {$judul}\n" : '').
                                ($tanggalPengajuan ? "📅 *Tanggal Pengajuan:* {$tanggalPengajuan}\n" : '').
                                "📍 *Status Terbaru:* {$statusLabel}\n".
                                (isset($this->record->solusi_admin) ? "📝 *Solusi Admin* : \n {$this->record->solusi_admin}\n" : '').
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
                                Log::error('Gagal mengirim pesan WhatsApp setelah ubah status pengaduan (view)', ['error' => $error]);
                                Notification::make()->title('Gagal kirim WhatsApp')->body($error)->danger()->send();
                            } else {
                                Notification::make()->title('Notifikasi WhatsApp terkirim')->success()->send();
                            }
                        }
                    }
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ComponentsSection::make('Detail Pengaduan')
                    ->schema([
                        TextEntry::make('pengaduan_id')->label('ID Pengaduan'),
                        TextEntry::make('warga.nama')->label('Nama Warga'),
                        TextEntry::make('jenisPengaduan.nama')->label('Jenis Pengaduan'),
                        TextEntry::make('tgl_pengajuan')->label('Tgl Pengajuan'),
                        TextEntry::make('status.keterangan')->label('Status'),
                        TextEntry::make('jdl_pengaduan')->label('Judul')->columnSpanFull(),
                        TextEntry::make('detail_pengaduan')->label('Detail')->columnSpanFull(),
                        ImageEntry::make('bukti')
                            ->label('')
                            ->view('filament.infolists.entries.surat-dokumen-viewer')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
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

    private function resolveDeviceToken(): ?string
    {
        $rtId = $this->record?->warga?->no_rt_id;
        if ($rtId) {
            $ketua = KetuaRt::query()->ketuaRt()->active()->where('no_rt_id', $rtId)->first();
            $hp = $ketua?->no_hp;
            if ($hp) {
                $targetDigits = preg_replace('/\D+/', '', (string) $hp);
                $devices = FonnteDevice::query()
                    ->where('status', 'connected')
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
        $active = FonnteDevice::query()->where('status', 'connected')->orderByDesc('last_synced_at')->value('token');

        return $active ?: env('DEVICE_TOKEN');
    }
}
