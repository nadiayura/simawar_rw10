<?php

namespace App\Filament\Resources\KegKesehatans\Pages;

use App\Filament\Resources\KegKesehatans\KegKesehatanResource;
use App\Models\FonnteDevice;
use App\Models\KegKesehatan;
use App\Models\Status;
use App\Models\Warga;
use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GroupedKegKesehatans extends Page
{
    protected static string $resource = KegKesehatanResource::class;

    protected static string $pluralLabel = 'Kegiatan Kesehatan';

    protected string $view = 'filament.resources.keg-kesehatans.pages.grouped-keg-kesehatans';

    public function getHeading(): string
    {
        return 'Kegiatan Kesehatan';
    }

    public function getTitle(): string
    {
        return 'Kegiatan Kesehatan';
    }

    public function getBreadcrumb(): string
    {
        return 'Kegiatan Kesehatan';
    }

    public function getBreadcrumbs(): array
    {
        return [
            KegKesehatanResource::getUrl('index') => 'Kegiatan Kesehatan',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('umumkanKegiatanKesehatan')
                ->label('Umumkan Kegiatan')
                ->icon('heroicon-o-megaphone')
                ->visible(function () {
                    $user = auth()->user();
                    if (! $user || ! $user->role) {
                        return false;
                    }

                    if ($user->role->isRT() && strtolower((string) $user->role->name) === 'rt') {
                        return false;
                    }

                    return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
                })
                ->form([
                    Select::make('jenis_kegiatan')
                        ->label('Jenis Kegiatan')
                        ->options(KegKesehatan::getJenisKegiatanOptions())
                        ->required(),
                    TextInput::make('nama_kegiatan')
                        ->label('Nama Kegiatan')
                        ->required(),
                    DatePicker::make('tgl')
                        ->label('Tanggal')
                        ->required(),
                    TextInput::make('penanggung_jawab')
                        ->label('Penanggung Jawab')
                        ->required(),
                    Select::make('status_id')
                        ->label('Status Kegiatan')
                        ->options(function () {
                            return Status::query()
                                ->where('fitur', 'keg_warga')
                                ->whereIn('keterangan', ['Dijadwalkan', 'Selesai'])
                                ->orderBy('keterangan')
                                ->pluck('keterangan', 'status_id')
                                ->toArray();
                        })
                        ->required()
                        ->reactive(),
                    Textarea::make('hasil_pelaksanaan')
                        ->label('Hasil Pelaksanaan')
                        ->rows(4)
                        ->columnSpanFull()
                        ->hidden(fn ($get) => strtolower(Status::query()->where('status_id', $get('status_id'))->value('keterangan') ?? '') !== 'selesai'),
                    FileUpload::make('dokumentasi')
                        ->label('Dokumentasi')
                        ->image()
                        ->directory('public/KegKesehatan')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(2048)
                        ->multiple()
                        ->reorderable()
                        ->columnSpanFull()
                        ->hidden(fn ($get) => strtolower(Status::query()->where('status_id', $get('status_id'))->value('keterangan') ?? '') !== 'selesai'),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();
                    if (! $user || ! $user->role || (! $user->role->isAdmin() && ! $user->role->isRW() && ! $user->role->isRT())) {
                        abort(403);
                    }
                    if ($user->role->isRT() && strtolower((string) $user->role->name) === 'rt') {
                        abort(403);
                    }

                    $statusName = strtolower(Status::query()->where('status_id', $data['status_id'] ?? null)->value('keterangan') ?? '');
                    if ($statusName === 'selesai') {
                        $missing = [];
                        if (empty($data['hasil_pelaksanaan'])) {
                            $missing['hasil_pelaksanaan'] = 'Hasil pelaksanaan wajib diisi saat status Selesai.';
                        }
                        if (empty($data['dokumentasi'])) {
                            $missing['dokumentasi'] = 'Dokumentasi wajib diunggah saat status Selesai.';
                        }
                        if ($missing) {
                            throw ValidationException::withMessages($missing);
                        }
                    }

                    $record = KegKesehatan::query()->create([
                        'jenis_kegiatan' => $data['jenis_kegiatan'],
                        'nama_kegiatan' => $data['nama_kegiatan'],
                        'tgl' => $data['tgl'],
                        'penanggung_jawab' => $data['penanggung_jawab'],
                        'jumlah_peserta' => 0,
                        'rincian_peserta' => KegKesehatan::getDefaultRincianPeserta(),
                        'aktivitas_dilakukan' => $data['hasil_pelaksanaan'] ?? '',
                        'hasil_pelaksanaan' => $data['hasil_pelaksanaan'] ?? null,
                        'dokumentasi' => $data['dokumentasi'] ?? [],
                        'status_id' => $data['status_id'],
                    ]);

                    try {
                        $this->sendKegiatanKesehatanAnnouncementWhatsApp($record, $data);
                    } catch (\Throwable $e) {
                        Log::error('Gagal mengirim WhatsApp pengumuman kegiatan kesehatan', ['error' => $e->getMessage()]);
                        Notification::make()->title('Gagal kirim WhatsApp')->body('Terjadi kesalahan saat mengirim WhatsApp')->danger()->send();
                    }
                }),
        ];
    }

    protected function sendKegiatanKesehatanAnnouncementWhatsApp(KegKesehatan $record, array $data): void
    {
        $deviceToken = $this->resolveDeviceToken();
        if (! $deviceToken) {
            Notification::make()->title('WhatsApp tidak dikirim')->body('Perangkat WhatsApp belum terhubung.')->warning()->send();

            return;
        }

        $user = auth()->user();
        $rtId = ($user && $user->role && $user->role->isRT()) ? ($user->warga?->no_rt_id) : null;

        $query = Warga::query()->whereNotNull('no_hp')->where('no_hp', '!=', '');
        if ($rtId) {
            $query->where('no_rt_id', $rtId);
        }

        $wargas = $query->get(['warga_nik', 'no_hp']);
        if ($wargas->isEmpty()) {
            return;
        }

        $tanggal = null;
        try {
            $tanggal = $record->tgl ? $record->tgl->format('d-m-Y') : (\Carbon\Carbon::parse((string) ($data['tgl'] ?? ''))->format('d-m-Y'));
        } catch (\Throwable $e) {
            $tanggal = $record->tgl ? $record->tgl->format('d-m-Y') : null;
        }

        $status = Status::query()->where('status_id', $record->status_id)->value('keterangan');

        $message =
            '📢 *Pengumuman Kegiatan Kesehatan*'."\n\n".
            ($record->nama_kegiatan ? 'Nama : '.$record->nama_kegiatan."\n" : '').
            ($record->jenis_kegiatan ? 'Jenis : '.$record->jenis_kegiatan."\n" : '').
            ($tanggal ? 'Tanggal : '.$tanggal."\n" : '').
            ($record->penanggung_jawab ? 'Penanggung Jawab : '.$record->penanggung_jawab."\n" : '').
            ($status ? 'Status : '.$status."\n" : '').
            "\n".'Silakan cek aplikasi untuk detail.';

        $sent = 0;
        $failed = 0;

        foreach ($wargas as $warga) {
            $target = $this->normalizePhone((string) $warga->no_hp);
            if ($target === '') {
                $failed++;

                continue;
            }

            $response = app(FonnteService::class)->sendWhatsAppMessage($target, $message, $deviceToken);
            $sendOk = ($response['status'] ?? false) && (! isset($response['data']['status']) || (bool) $response['data']['status']);

            if ($sendOk) {
                $sent++;
            } else {
                $failed++;
            }
        }

        if ($sent > 0) {
            $body = 'Terkirim ke '.$sent.' warga';
            if ($failed > 0) {
                $body .= ', gagal '.$failed;
            }
            Notification::make()->title('WhatsApp terkirim')->body($body)->success()->send();
        } else {
            Notification::make()->title('WhatsApp gagal dikirim')->body('Tidak ada pesan yang berhasil dikirim.')->danger()->send();
        }
    }

    protected function resolveDeviceToken(): ?string
    {
        $token = FonnteDevice::query()
            ->whereIn('status', ['connected', 'connect'])
            ->orderByDesc('last_synced_at')
            ->value('token');

        if ($token) {
            return $token;
        }

        return FonnteDevice::query()
            ->whereIn('status', ['connected', 'connect'])
            ->latest('updated_at')
            ->value('token');
    }

    protected function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);
        if (! is_string($digits) || $digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    protected function getViewData(): array
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $records = KegKesehatan::query()
            ->with('status')
            ->orderByDesc('tgl')
            ->get();

        $groupMap = [];

        foreach ($records as $k) {
            if (! $k->tgl) {
                continue;
            }

            $tahun = (int) $k->tgl->format('Y');
            $bulan = (int) $k->tgl->format('n');

            $label = ($months[$bulan] ?? (string) $bulan).' '.$tahun;
            $key = sprintf('%04d-%02d', $tahun, $bulan);

            if (! isset($groupMap[$key])) {
                $groupMap[$key] = [
                    'bulan_label' => $label,
                    'rows' => [],
                ];
            }

            $statusName = strtolower($k->status?->keterangan ?? '');

            $groupMap[$key]['rows'][] = [
                'id' => $k->getKey(),
                'jenis_kegiatan' => $k->jenis_kegiatan,
                'nama_kegiatan' => $k->nama_kegiatan,
                'penanggung_jawab' => $k->penanggung_jawab,
                'status' => $k->status?->keterangan,
                'tanggal' => optional($k->tgl)->format('Y-m-d'),
                'edit_label' => in_array($statusName, ['dijadwalkan', 'terjadwal', 'berlangsung'], true)
                    ? 'Lengkapi Info'
                    : 'Edit',
            ];
        }

        ksort($groupMap);
        $groups = array_values($groupMap);

        return [
            'groups' => $groups,
        ];
    }

    public function deleteKegiatan(string $id): void
    {
        $record = KegKesehatan::query()->findOrFail($id);

        if (! KegKesehatanResource::canDelete($record)) {
            abort(403);
        }

        $record->delete();

        Notification::make()
            ->title('Kegiatan Kesehatan berhasil dihapus')
            ->success()
            ->send();
    }
}
