<?php

namespace App\Filament\Resources\SuratKetWargas\Pages;

use App\Filament\Resources\SuratKetWargas\SuratKetWargaResource;
use App\Models\FonnteDevice;
use App\Models\KetuaRt;
use App\Models\Status;
use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;

class ViewSuratKetWarga extends ViewRecord
{
    protected static string $resource = SuratKetWargaResource::class;

    public function mount($record): void
    {
        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('proses_surat')
                ->label('Proses Surat Keterangan')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->form([
                    Select::make('status_id')
                        ->label('Status')
                        ->options(fn () => Status::query()->where('fitur', 'surat')->orderBy('keterangan')->pluck('keterangan', 'status_id')->toArray())
                        ->default(fn () => $this->record->status_id)
                        ->required()
                        ->live(),
                    DatePicker::make('tgl_selesai')
                        ->label('Tgl Selesai')
                        ->visible(fn ($get) => strtolower((string) optional(Status::find($get('status_id')))->keterangan) === 'selesai'),
                    Textarea::make('catatan_admin')
                        ->label('Catatan Admin')
                        ->default(fn () => $this->record->catatan_admin)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $this->record->status_id = $data['status_id'] ?? $this->record->status_id;
                    if (array_key_exists('tgl_selesai', $data)) {
                        $this->record->tgl_selesai = $data['tgl_selesai'];
                    }
                    if (array_key_exists('catatan_admin', $data)) {
                        $this->record->catatan_admin = $data['catatan_admin'];
                    }
                    $this->record->save();

                    Notification::make()->title('Status surat diperbarui')->success()->send();

                    $this->record->refresh()->loadMissing(['warga', 'jenisSurat', 'status']);

                    $warga = $this->record->warga;
                    if (! $warga || empty($warga->no_hp)) {
                        Notification::make()->title('WhatsApp tidak dikirim')->body('Nomor HP warga belum diisi')->warning()->send();

                        return;
                    }

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
                        (isset($this->record->catatan_admin) ? "📝 *Pesan SIMAWAR* : \n {$this->record->catatan_admin}\n" : '').
                        "\nSilakan cek aplikasi untuk detail lebih lanjut. Terima kasih 🙏";

                    $rawNoHp = (string) $warga->no_hp;
                    $target = $this->normalizePhone($rawNoHp);
                    if ($target === '') {
                        Notification::make()->title('Gagal kirim WhatsApp')->body('Format nomor HP warga tidak valid')->danger()->send();

                        return;
                    }

                    try {
                        Log::info('Mengirim WhatsApp pembaruan surat (view)', [
                            'surat_ket_warga_id' => $this->record->getKey(),
                            'warga_nik' => $warga->getKey(),
                            'raw_no_hp' => $rawNoHp,
                            'target' => $target,
                        ]);

                        $service = app(FonnteService::class);
                        $response = $service->sendWhatsAppMessage(
                            $target,
                            $message,
                            $deviceToken
                        );
                    } catch (\Throwable $e) {
                        Log::error('Exception saat mengirim WhatsApp setelah ubah status surat (view)', ['error' => $e->getMessage()]);
                        Notification::make()->title('Gagal kirim WhatsApp')->body('Terjadi kesalahan saat mengirim WhatsApp')->danger()->send();

                        return;
                    }

                    $sendOk = ($response['status'] ?? false) && (! isset($response['data']['status']) || (bool) $response['data']['status']);
                    if (! $sendOk) {
                        $error = $response['error'] ?? ($response['data']['reason'] ?? 'Unknown error');
                        $fallbackTarget = $this->normalizePhoneInternational($rawNoHp);
                        if ($fallbackTarget !== '' && $fallbackTarget !== $target) {
                            try {
                                Log::info('Mencoba ulang WhatsApp pembaruan surat (view) dengan fallback target', [
                                    'surat_ket_warga_id' => $this->record->getKey(),
                                    'warga_nik' => $warga->getKey(),
                                    'raw_no_hp' => $rawNoHp,
                                    'target' => $fallbackTarget,
                                    'prev_error' => $error,
                                ]);

                                $response = $service->sendWhatsAppMessage(
                                    $fallbackTarget,
                                    $message,
                                    $deviceToken
                                );
                            } catch (\Throwable $e) {
                                Log::error('Exception saat retry WhatsApp setelah ubah status surat (view)', ['error' => $e->getMessage()]);
                                Notification::make()->title('Gagal kirim WhatsApp')->body('Terjadi kesalahan saat mengirim WhatsApp')->danger()->send();

                                return;
                            }

                            $sendOk = ($response['status'] ?? false) && (! isset($response['data']['status']) || (bool) $response['data']['status']);
                            if (! $sendOk) {
                                $error = $response['error'] ?? ($response['data']['reason'] ?? 'Unknown error');
                                Log::error('Gagal mengirim pesan WhatsApp setelah ubah status surat (view)', ['error' => $error]);
                                Notification::make()->title('Gagal kirim WhatsApp')->body($error)->danger()->send();

                                return;
                            }

                            $target = $fallbackTarget;
                        } else {
                            Log::error('Gagal mengirim pesan WhatsApp setelah ubah status surat (view)', ['error' => $error]);
                            Notification::make()->title('Gagal kirim WhatsApp')->body($error)->danger()->send();

                            return;
                        }
                    }

                    $requestId = $response['data']['requestid'] ?? null;
                    $reason = $response['data']['reason'] ?? null;
                    $body = 'Tujuan: '.$target.($requestId ? ' • Request ID: '.$requestId : '').($reason ? ' • '.$reason : '');
                    Notification::make()->title('Notifikasi WhatsApp terkirim')->body($body)->success()->send();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ComponentsSection::make('Surat Keterangan')
                    ->schema([
                        TextEntry::make('surat_ket_warga_id')->label('ID Surat'),
                        TextEntry::make('warga.nama')->label('Nama Warga'),
                        TextEntry::make('jenisSurat.nama_surat')->label('Jenis Surat'),
                        TextEntry::make('tgl_pengajuan')->label('Tgl Pengajuan'),
                        TextEntry::make('tgl_acara_mulai')
                            ->label('Tgl Mulai Acara')
                            ->visible(fn ($record) => filled($record?->tgl_acara_mulai)),
                        TextEntry::make('tgl_acara_selesai')
                            ->label('Tgl Selesai Acara')
                            ->visible(fn ($record) => filled($record?->tgl_acara_selesai)),
                        TextEntry::make('status.keterangan')->label('Status'),
                        TextEntry::make('keperluan')->label('Keperluan'),
                        ImageEntry::make('dok_pendukung')
                            ->label('Dokumen Pendukung')
                            ->view('filament.infolists.entries.surat-dokumen-viewer')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    private function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\\D+/', '', $raw);

        if ($digits === null) {
            return '';
        }

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '620')) {
            $digits = '62'.substr($digits, 3);
        } elseif (str_starts_with($digits, '62') && substr($digits, 2, 1) === '0') {
            $digits = '62'.substr($digits, 3);
        }

        if (str_starts_with($digits, '8')) {
            $digits = '0'.$digits;
        }

        if (! (str_starts_with($digits, '0') || str_starts_with($digits, '62'))) {
            return '';
        }

        if (strlen($digits) < 10) {
            return '';
        }

        return $digits;
    }

    private function normalizePhoneInternational(string $raw): string
    {
        $digits = $this->normalizePhone($raw);
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
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
}
