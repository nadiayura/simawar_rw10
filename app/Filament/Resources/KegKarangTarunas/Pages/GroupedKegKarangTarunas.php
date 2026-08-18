<?php

namespace App\Filament\Resources\KegKarangTarunas\Pages;

use App\Filament\Resources\KegKarangTarunas\KegKarangTarunaResource;
use App\Models\FonnteDevice;
use App\Models\KegKarangTaruna;
use App\Models\NoRt;
use App\Models\Status;
use App\Models\Warga;
use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GroupedKegKarangTarunas extends Page
{
    protected static string $resource = KegKarangTarunaResource::class;

    protected static string $pluralLabel = 'Kegiatan Karang Taruna';

    protected string $view = 'filament.resources.keg-karang-tarunas.pages.grouped-keg-karang-tarunas';

    public function getHeading(): string
    {
        return 'Kegiatan Karang Taruna';
    }

    public function getTitle(): string
    {
        return 'Kegiatan Karang Taruna';
    }

    public function getBreadcrumb(): string
    {
        return 'Kegiatan Karang Taruna';
    }

    public function getBreadcrumbs(): array
    {
        return [
            KegKarangTarunaResource::getUrl('index') => 'Kegiatan Karang Taruna',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('umumkanKegiatan')
                ->label('Umumkan Kegiatan')
                ->icon('heroicon-o-megaphone')
                ->visible(function () {
                    $user = auth()->user();
                    if (! $user || ! $user->role) {
                        return false;
                    }

                    return $user->role->isAdmin() || $user->role->isRT();
                })
                ->form([
                    TextInput::make('nama_kegiatan')->label('Nama Kegiatan')->required(),
                    DateTimePicker::make('tanggal')->label('Tanggal')->required(),
                    Select::make('status_id')
                        ->label('Status')
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
                    Textarea::make('deskripsi')
                        ->label('Deskripsi')
                        ->hidden(fn ($get) => strtolower(Status::query()->where('status_id', $get('status_id'))->value('keterangan') ?? '') !== 'selesai'),
                    Select::make('penanggung_jawab')
                        ->label('Penanggung Jawab')
                        ->options(function () {
                            $user = auth()->user();
                            $rtId = optional($user?->warga)->no_rt_id;

                            if (! $rtId) {
                                if (! $user || ! $user->role || (! $user->role->isAdmin() && ! $user->role->isRW())) {
                                    return [];
                                }

                                return Warga::query()
                                    ->orderBy('nama')
                                    ->get(['warga_nik', 'no_rt_id', 'nama'])
                                    ->mapWithKeys(function (Warga $w) {
                                        return [$w->warga_nik => trim(($w->no_rt_id ? $w->no_rt_id.' - ' : '').(string) $w->nama)];
                                    })
                                    ->toArray();
                            }

                            return Warga::query()
                                ->where('no_rt_id', $rtId)
                                ->orderBy('nama')
                                ->pluck('nama', 'warga_nik')
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),
                    FileUpload::make('dokumentasi')
                        ->label('Dokumentasi')
                        ->image()
                        ->directory('public/KarangTaruna')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(2048)
                        ->multiple()
                        ->reorderable()
                        ->hidden(fn ($get) => strtolower(Status::query()->where('status_id', $get('status_id'))->value('keterangan') ?? '') !== 'selesai'),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();
                    if (! $user || ! $user->role || (! $user->role->isAdmin() && ! $user->role->isRT())) {
                        abort(403);
                    }

                    $statusName = strtolower(Status::query()->where('status_id', $data['status_id'] ?? null)->value('keterangan') ?? '');
                    if ($statusName === 'selesai') {
                        $missing = [];
                        if (empty($data['deskripsi'])) {
                            $missing['deskripsi'] = 'Deskripsi wajib diisi saat status Selesai.';
                        }
                        if (empty($data['dokumentasi'])) {
                            $missing['dokumentasi'] = 'Dokumentasi wajib diunggah saat status Selesai.';
                        }
                        if ($missing) {
                            throw ValidationException::withMessages($missing);
                        }
                    }

                    KegKarangTaruna::query()->create([
                        'nama_kegiatan' => $data['nama_kegiatan'],
                        'tanggal' => $data['tanggal'],
                        'penanggung_jawab' => $data['penanggung_jawab'],
                        'status_id' => $data['status_id'],
                        'deskripsi' => $data['deskripsi'] ?? null,
                        'dokumentasi' => $data['dokumentasi'] ?? [],
                    ]);

                    try {
                        $this->sendKegiatanKarangTarunaAnnouncementWhatsApp($data);
                    } catch (\Throwable $e) {
                        Log::error('Gagal mengirim WhatsApp pengumuman kegiatan karang taruna', ['error' => $e->getMessage()]);
                        Notification::make()->title('Gagal kirim WhatsApp')->body('Terjadi kesalahan saat mengirim WhatsApp')->danger()->send();
                    }
                }),
        ];
    }

    protected function sendKegiatanKarangTarunaAnnouncementWhatsApp(array $data): void
    {
        $deviceToken = $this->resolveDeviceToken();
        if (! $deviceToken) {
            Notification::make()->title('WhatsApp tidak dikirim')->body('Perangkat WhatsApp belum terhubung.')->warning()->send();

            return;
        }

        $rtId = null;
        $pjNik = $data['penanggung_jawab'] ?? null;
        if ($pjNik) {
            $rtId = Warga::query()->where('warga_nik', $pjNik)->value('no_rt_id');
        }
        if (! $rtId) {
            $user = auth()->user();
            if ($user && $user->role && $user->role->isRT()) {
                $rtId = $user->warga?->no_rt_id;
            }
        }

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
            $tanggal = \Carbon\Carbon::parse((string) ($data['tanggal'] ?? ''))->format('d-m-Y H:i');
        } catch (\Throwable $e) {
            $tanggal = null;
        }

        $status = null;
        if (! empty($data['status_id'])) {
            $status = Status::query()->where('status_id', $data['status_id'])->value('keterangan');
        }

        $rtNomor = $rtId ? (NoRt::find($rtId)?->nomor ?? $rtId) : null;
        $pjNama = null;
        if ($pjNik) {
            $pjNama = Warga::query()->where('warga_nik', $pjNik)->value('nama');
        }

        $message =
            '📢 *Pengumuman Kegiatan Karang Taruna*'."\n\n".
            (! empty($data['nama_kegiatan']) ? 'Nama : '.$data['nama_kegiatan']."\n" : '').
            ($rtNomor ? 'RT : '.$rtNomor."\n" : '').
            ($tanggal ? 'Tanggal : '.$tanggal."\n" : '').
            ($pjNama ? 'Penanggung Jawab : '.$pjNama."\n" : '').
            ($status ? 'Status : '.$status."\n" : '').
            (! empty($data['deskripsi']) ? "\n".'Info : '.$data['deskripsi']."\n" : '').
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
        $user = Auth::user();
        $selectedRtId = request()->query('no_rt_id');
        $isRt = $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id;

        $rtOptionsQuery = NoRt::query()->orderBy('nomor');
        if ($isRt) {
            $rtOptionsQuery->where('no_rt_id', $user->warga->no_rt_id);
        }
        $rtOptions = $rtOptionsQuery->pluck('nomor', 'no_rt_id')->toArray();
        if ($isRt && (! $selectedRtId || ! array_key_exists((string) $selectedRtId, $rtOptions))) {
            $selectedRtId = $user->warga->no_rt_id;
        }

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

        $query = KegKarangTaruna::query()
            ->with(['status', 'pjWarga'])
            ->orderByDesc('tanggal');

        if ($selectedRtId) {
            $query->whereHas('pjWarga', function ($q) use ($selectedRtId) {
                $q->where('no_rt_id', $selectedRtId);
            });
        } elseif ($isRt) {
            $query->whereHas('pjWarga', function ($q) use ($user) {
                $q->where('no_rt_id', $user->warga->no_rt_id);
            });
        }

        $records = $query->get();

        $groupMap = [];

        foreach ($records as $k) {
            if (! $k->tanggal) {
                continue;
            }

            $tahun = (int) $k->tanggal->format('Y');
            $bulan = (int) $k->tanggal->format('n');

            $label = ($months[$bulan] ?? (string) $bulan).' '.$tahun;
            $key = sprintf('%04d-%02d', $tahun, $bulan);

            if (! isset($groupMap[$key])) {
                $groupMap[$key] = [
                    'bulan_label' => $label,
                    'rows' => [],
                ];
            }

            $statusName = strtolower($k->status?->keterangan ?? '');
            $rtId = (string) ($k->pjWarga?->no_rt_id ?? '');
            $rtNomor = $rtId !== '' ? ($rtOptions[$rtId] ?? null) : null;
            $rtLabel = $rtNomor !== null && $rtNomor !== '' ? ('RT '.str_pad((string) $rtNomor, 3, '0', STR_PAD_LEFT)) : '-';

            $groupMap[$key]['rows'][] = [
                'id' => $k->getKey(),
                'nama_kegiatan' => $k->nama_kegiatan,
                'rt' => $rtLabel,
                'penanggung_jawab' => $k->pjWarga?->nama,
                'status' => $k->status?->keterangan,
                'tanggal' => optional($k->tanggal)->format('Y-m-d H:i'),
                'edit_label' => in_array($statusName, ['dijadwalkan', 'terjadwal', 'berlangsung'], true)
                    ? 'Lengkapi Info'
                    : 'Edit',
            ];
        }

        ksort($groupMap);
        $groups = array_values($groupMap);

        return [
            'groups' => $groups,
            'rtOptions' => $rtOptions,
            'selectedRtId' => $selectedRtId,
        ];
    }

    public function deleteKegiatan(string $id): void
    {
        $record = KegKarangTaruna::query()->findOrFail($id);

        if (! KegKarangTarunaResource::canDelete($record)) {
            abort(403);
        }

        $record->delete();

        Notification::make()
            ->title('Kegiatan Karang Taruna berhasil dihapus')
            ->success()
            ->send();
    }
}
