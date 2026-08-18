<?php

namespace App\Filament\Resources\TagihanIuranWargas\Pages;

use App\Filament\Resources\TagihanIuranWargas\TagihanIuranWargaResource;
use App\Models\Iuran;
use App\Models\NoRt;
use App\Models\PeriodeIuran;
use App\Models\Status;
use App\Models\TagihanIuranWarga;
use App\Models\Warga;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupedTagihanIuranWargas extends Page
{
    protected static string $resource = TagihanIuranWargaResource::class;

    protected static string $pluralLabel = 'Tagihan Iuran Warga';

    protected string $view = 'filament.resources.tagihan-iuran-wargas.pages.grouped-tagihan-iuran-wargas';

    public function getHeading(): string
    {
        return 'Tagihan Iuran Warga';
    }

    public function getTitle(): string
    {
        return 'Tagihan Iuran Warga';
    }

    public function getBreadcrumb(): string
    {
        return 'Tagihan Iuran Warga';
    }

    public function getBreadcrumbs(): array
    {
        return [
            TagihanIuranWargaResource::getUrl('index') => 'Tagihan Iuran Warga',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('broadcast_reminder')
                ->label('Kirim Broadcast Pengingat')
                ->action(function (): void {
                    $user = Auth::user();
                    $wargaBelumBayar = Warga::whereHas('tagihanIuranWarga', function ($query) {
                        $query->whereHas('status', function ($q) {
                            $q->where('keterangan', 'Belum bayar');
                        });
                    })
                        ->when(
                            $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id,
                            fn ($q) => $q->where('no_rt_id', $user->warga->no_rt_id)
                        )
                        ->get();

                    $deviceToken = env('DEVICE_TOKEN');

                    $success = 0;
                    $failed = 0;

                    foreach ($wargaBelumBayar as $warga) {
                        if (empty($warga->no_hp)) {
                            continue;
                        }
                        $response = app(\App\Services\FonnteService::class)->sendWhatsAppMessage(
                            $warga->no_hp,
                            "📢 *Pemberitahuan Iuran*\n\n"
                            ."Halo {$warga->nama}, kami menginformasikan bahwa terdapat iuran yang masih belum dibayar.\n"
                            ."Mohon segera melakukan pembayaran agar layanan tetap berjalan lancar.\n\n"
                            .'Terima kasih atas kerja samanya 🙏',
                            $deviceToken
                        );

                        if (! ($response['status'] ?? false) || (isset($response['data']['status']) && ! $response['data']['status'])) {
                            Notification::make()
                                ->title("Gagal mengirim pengingat ke {$warga->nama}")
                                ->body($response['error'] ?? 'Unknown error')
                                ->danger()
                                ->send();
                            $failed++;
                        } else {
                            $success++;
                        }
                    }

                    Notification::make()
                        ->title('Broadcast selesai')
                        ->body('Berhasil: '.$success.' • Gagal: '.$failed)
                        ->success()
                        ->send();
                }),
            Action::make('generate_tagihan')
                ->label('Generate Tagihan Otomatis')
                ->form([
                    Toggle::make('semua_warga')
                        ->label('Semua Warga')
                        ->inline(false),
                    Select::make('warga_nik')
                        ->label('Warga')
                        ->multiple()
                        ->searchable()
                        ->hidden(fn ($get) => (bool) $get('semua_warga') === true)
                        ->required(fn ($get) => (bool) $get('semua_warga') !== true)
                        ->options(function () {
                            $query = Warga::query();

                            $user = Auth::user();
                            if ($user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id) {
                                $query->where('no_rt_id', $user->warga->no_rt_id);
                            }

                            return $query
                                ->orderBy('nama')
                                ->get()
                                ->mapWithKeys(function (Warga $warga) {
                                    return [
                                        $warga->warga_nik => $warga->nama.' - '.\App\Models\Warga::maskedNik($warga->warga_nik),
                                    ];
                                })
                                ->toArray();
                        })
                        ->required(),
                    Select::make('periode_iuran_id')
                        ->label('Periode Iuran')
                        ->multiple()
                        ->searchable()
                        ->options(function () {
                            $map = [
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

                            return PeriodeIuran::query()
                                ->orderBy('tahun')
                                ->orderBy('bulan')
                                ->get()
                                ->mapWithKeys(function (PeriodeIuran $periode) use ($map) {
                                    $label = ($map[(int) $periode->bulan] ?? $periode->bulan).' '.$periode->tahun;

                                    return [
                                        $periode->periode_iuran_id => $label,
                                    ];
                                })
                                ->toArray();
                        })
                        ->required(),
                ])
                ->action(function (array $data): void {
                    DB::beginTransaction();
                    try {
                        $wargaNiks = (array) ($data['warga_nik'] ?? []);
                        $periodeIds = (array) ($data['periode_iuran_id'] ?? []);

                        if (($data['semua_warga'] ?? false) === true) {
                            $query = Warga::query();
                            $user = Auth::user();
                            if ($user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id) {
                                $query->where('no_rt_id', $user->warga->no_rt_id);
                            }
                            $wargaNiks = $query->pluck('warga_nik')->all();
                        }

                        if (empty($wargaNiks) || empty($periodeIds)) {
                            Notification::make()
                                ->title('Data belum lengkap')
                                ->body('Silakan pilih warga (atau centang Semua Warga) dan periode iuran.')
                                ->danger()
                                ->send();
                            DB::rollBack();

                            return;
                        }

                        $wargas = Warga::query()
                            ->whereIn('warga_nik', $wargaNiks)
                            ->get()
                            ->keyBy('warga_nik');

                        if ($wargas->isEmpty()) {
                            Notification::make()
                                ->title('Data warga tidak ditemukan')
                                ->body('Silakan periksa kembali pilihan warga.')
                                ->danger()
                                ->send();
                            DB::rollBack();

                            return;
                        }

                        $invalidWarga = $wargas
                            ->filter(fn (Warga $warga) => empty($warga->iuran_id))
                            ->pluck('nama')
                            ->toArray();

                        if (! empty($invalidWarga)) {
                            Notification::make()
                                ->title('Jenis iuran belum diatur')
                                ->body('Beberapa warga belum memiliki jenis iuran: '.implode(', ', $invalidWarga))
                                ->danger()
                                ->send();
                            DB::rollBack();

                            return;
                        }

                        $periodeRecords = PeriodeIuran::query()
                            ->whereIn('periode_iuran_id', $periodeIds)
                            ->get()
                            ->keyBy('periode_iuran_id');

                        if ($periodeRecords->isEmpty()) {
                            Notification::make()
                                ->title('Periode iuran tidak ditemukan')
                                ->body('Silakan periksa kembali periode yang dipilih.')
                                ->danger()
                                ->send();
                            DB::rollBack();

                            return;
                        }

                        $created = 0;
                        $skippedPeriodeIds = [];
                        $existingPairs = TagihanIuranWarga::query()
                            ->whereIn('warga_nik', $wargaNiks)
                            ->whereIn('periode_iuran_id', $periodeIds)
                            ->get(['warga_nik', 'periode_iuran_id'])
                            ->mapWithKeys(fn ($row) => [((string) $row->warga_nik).'|'.((string) $row->periode_iuran_id) => true])
                            ->all();

                        $iuranAmounts = Iuran::query()
                            ->whereIn('iuran_id', $wargas->pluck('iuran_id')->filter()->unique()->values()->all())
                            ->get(['iuran_id', 'jumlah_default'])
                            ->mapWithKeys(fn (Iuran $iuran) => [(string) $iuran->iuran_id => $iuran->jumlah_default])
                            ->all();

                        $statusId = Status::idForFitur('keuangan', 'Belum bayar');
                        $now = now();
                        $day = $now->format('d');

                        $prefixSeq = [];
                        foreach ($periodeIds as $periodeId) {
                            $per = $periodeRecords->get($periodeId);
                            if (! $per) {
                                continue;
                            }
                            $mm = str_pad((string) ((int) $per->bulan), 2, '0', STR_PAD_LEFT);
                            $yyyy = (string) ((int) $per->tahun);
                            $base = 'TG-'.$day.$mm.$yyyy.'-';

                            $last = TagihanIuranWarga::query()
                                ->where('tagihan_iuran_id', 'like', $base.'%')
                                ->orderByDesc('tagihan_iuran_id')
                                ->lockForUpdate()
                                ->value('tagihan_iuran_id');

                            $seq = 1;
                            if (is_string($last) && str_starts_with($last, $base)) {
                                $suffix = substr($last, strlen($base));
                                $num = (int) $suffix;
                                if ($num > 0) {
                                    $seq = $num + 1;
                                }
                            }

                            $prefixSeq[$base] = $seq;
                        }

                        $rows = [];
                        foreach ($wargaNiks as $wargaNik) {
                            $warga = $wargas->get($wargaNik);
                            if (! $warga) {
                                continue;
                            }
                            $iuranId = (string) $warga->iuran_id;
                            $nominal = $iuranAmounts[$iuranId] ?? null;

                            foreach ($periodeIds as $periodeId) {
                                $key = ((string) $wargaNik).'|'.((string) $periodeId);
                                if (isset($existingPairs[$key])) {
                                    $skippedPeriodeIds[$periodeId] = true;

                                    continue;
                                }
                                $per = $periodeRecords->get($periodeId);
                                if (! $per) {
                                    continue;
                                }
                                $mm = str_pad((string) ((int) $per->bulan), 2, '0', STR_PAD_LEFT);
                                $yyyy = (string) ((int) $per->tahun);
                                $base = 'TG-'.$day.$mm.$yyyy.'-';
                                $seq = $prefixSeq[$base] ?? 1;
                                $prefixSeq[$base] = $seq + 1;

                                $rows[] = [
                                    'tagihan_iuran_id' => $base.str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
                                    'warga_nik' => $wargaNik,
                                    'iuran_id' => $iuranId,
                                    'periode_iuran_id' => $periodeId,
                                    'nominal_tagihan' => $nominal,
                                    'status_id' => $statusId,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                                $created++;
                            }
                        }

                        if (! empty($rows)) {
                            foreach (array_chunk($rows, 500) as $chunk) {
                                DB::table((new TagihanIuranWarga)->getTable())->insert($chunk);
                            }
                        }

                        DB::commit();

                        if ($created > 0) {
                            Notification::make()
                                ->title('Generate tagihan berhasil')
                                ->body('Jumlah dibuat: '.$created)
                                ->success()
                                ->send();
                        }

                        if (! empty($skippedPeriodeIds)) {
                            $labels = [];
                            foreach (array_keys($skippedPeriodeIds) as $pid) {
                                $periode = $periodeRecords->get($pid);
                                if ($periode) {
                                    $labels[] = ($periode->nama_bulan ?? (string) $periode->bulan).' '.$periode->tahun;
                                }
                            }

                            if (! empty($labels)) {
                                $labels = array_values(array_unique($labels));
                                Notification::make()
                                    ->title('Beberapa bulan sudah memiliki tagihan')
                                    ->body('Tagihan sudah ada untuk bulan: '.implode(', ', $labels))
                                    ->warning()
                                    ->send();
                            }
                        }

                        if ($created === 0 && empty($skippedPeriodeIds)) {
                            Notification::make()
                                ->title('Tidak ada tagihan yang diproses')
                                ->body('Tidak ada kombinasi warga dan periode yang valid.')
                                ->warning()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        Notification::make()->title('Gagal generate tagihan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        $sort = request()->query('sort', 'nominal_desc');
        $sort = in_array($sort, ['nominal_desc', 'rt_asc', 'rt_desc'], true) ? $sort : 'nominal_desc';
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

        $periodes = PeriodeIuran::query()
            ->orderBy('bulan')
            ->orderBy('tahun')
            ->get();

        $years = [];
        foreach ($periodes as $periode) {
            $tahun = (int) $periode->tahun;
            if (! isset($years[$tahun])) {
                $years[$tahun] = [
                    'year' => $tahun,
                    'months' => [],
                ];
            }

            $query = TagihanIuranWarga::query()
                ->with(['warga', 'status', 'pembayaranTunai'])
                ->where('periode_iuran_id', $periode->periode_iuran_id);

            if ($sort === 'rt_asc' || $sort === 'rt_desc') {
                $dir = $sort === 'rt_desc' ? 'desc' : 'asc';
                $query->leftJoin('wargas', 'wargas.warga_nik', '=', 'tagihan_iuran_wargas.warga_nik')
                    ->leftJoin('no_rts', 'no_rts.no_rt_id', '=', 'wargas.no_rt_id')
                    ->orderBy('no_rts.nomor', $dir)
                    ->select('tagihan_iuran_wargas.*');
            } else {
                $query->orderBy('nominal_tagihan', 'desc');
            }

            if ($selectedRtId) {
                $query->whereHas('warga', function ($q) use ($selectedRtId) {
                    $q->where('no_rt_id', $selectedRtId);
                });
            } elseif ($isRt) {
                $query->whereHas('warga', function ($q) use ($user) {
                    $q->where('no_rt_id', $user->warga->no_rt_id);
                });
            }

            $rows = $query->get()->map(function (TagihanIuranWarga $t) {
                $statusLabel = $t->status?->keterangan;
                if ($t->pembayaranTunai && ($t->pembayaranTunai->status_id === Status::idForFitur('keuangan', 'settlement'))) {
                    $statusLabel = 'settlement';
                }

                return [
                    'id' => $t->tagihan_iuran_id,
                    'warga' => $t->warga?->nama,
                    'nominal' => $t->nominal_tagihan,
                    'status' => $statusLabel,
                    'tanggal_lunas' => $t->tanggal_lunas,
                    'has_tunai' => (bool) $t->PembayaranTunai_id,
                    'bukti_tunai' => is_array($t->pembayaranTunai?->bukti) ? $t->pembayaranTunai->bukti : [],
                ];
            })->toArray();

            $monthLabel = $periode->nama_bulan ?? (string) $periode->bulan;

            $years[$tahun]['months'][] = [
                'bulan_label' => $monthLabel,
                'periode_id' => $periode->periode_iuran_id,
                'rows' => $rows,
            ];
        }

        krsort($years);

        return [
            'years' => array_values($years),
            'sort' => $sort,
            'rtOptions' => $rtOptions,
            'selectedRtId' => $selectedRtId,
        ];
    }

    public function deleteTagihan(string $id): void
    {
        $record = TagihanIuranWarga::query()->findOrFail($id);

        if (! TagihanIuranWargaResource::canDelete($record)) {
            abort(403);
        }

        $record->delete();

        Notification::make()
            ->title('Tagihan iuran berhasil dihapus')
            ->success()
            ->send();
    }
}
