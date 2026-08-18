<?php

namespace App\Filament\Resources\SuratKetWargas\Pages;

use App\Filament\Resources\SuratKetWargas\SuratKetWargaResource;
use App\Models\Status;
use App\Models\SuratKetWarga;
use App\Models\Warga;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class GroupedSuratKetWargas extends Page
{
    protected static string $resource = SuratKetWargaResource::class;

    protected static string $pluralLabel = 'Pengajuan Surat Warga';

    protected string $view = 'filament.resources.surat-ket-wargas.pages.grouped-surat-ket-wargas';

    public function getHeading(): string
    {
        return 'Pengajuan Surat Warga';
    }

    public function getTitle(): string
    {
        return 'Pengajuan Surat Warga';
    }

    public function getBreadcrumb(): string
    {
        return 'Pengajuan Surat Warga';
    }

    public function getBreadcrumbs(): array
    {
        return [
            SuratKetWargaResource::getUrl('index') => 'Pengajuan Surat Warga',
        ];
    }

    protected function getViewData(): array
    {
        $user = Auth::user();
        $onlyRtId = ($user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id)
            ? $user->warga->no_rt_id
            : null;

        $range = request()->query('range');
        $start = request()->query('start');
        $end = request()->query('end');

        $startDate = null;
        $endDate = null;
        if ($range === 'month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif ($range === 'week') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($range === 'year') {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        } elseif ($start && $end) {
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $end)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = null;
                $endDate = null;
            }
        }

        $summaryBaseQuery = SuratKetWarga::query()
            ->when($onlyRtId, function ($q) use ($onlyRtId) {
                $q->whereHas('warga', function ($w) use ($onlyRtId) {
                    $w->where('no_rt_id', $onlyRtId);
                });
            });

        if ($startDate && $endDate) {
            $summaryBaseQuery->whereBetween('tgl_pengajuan', [$startDate, $endDate]);
        }

        $totalSurat = (clone $summaryBaseQuery)->count();

        $selesaiStatusId = Status::idForFitur('surat', 'selesai')
            ?? Status::idForFitur('surat_ket_warga', 'selesai')
            ?? Status::idByName('selesai');

        $selesaiQuery = (clone $summaryBaseQuery)
            ->when(
                $selesaiStatusId,
                fn ($q) => $q->where('status_id', $selesaiStatusId),
                fn ($q) => $q->whereHas('status', function ($s) {
                    $s->whereRaw('LOWER(keterangan) = ?', ['selesai']);
                })
            );

        $suratSelesai = (clone $selesaiQuery)->count();

        $suratBelumSelesai = $totalSurat - $suratSelesai;

        $totalByJenis = (clone $summaryBaseQuery)
            ->with(['jenisSurat'])
            ->get()
            ->groupBy(function (SuratKetWarga $s) {
                return (string) ($s->jenisSurat?->nama_surat ?? 'Lainnya');
            })
            ->map->count()
            ->toArray();

        $selesaiByJenis = (clone $selesaiQuery)
            ->with(['jenisSurat'])
            ->get()
            ->groupBy(function (SuratKetWarga $s) {
                return (string) ($s->jenisSurat?->nama_surat ?? 'Lainnya');
            })
            ->map->count()
            ->toArray();

        $belumSelesaiByJenis = [];
        foreach ($totalByJenis as $jenis => $count) {
            $done = $selesaiByJenis[$jenis] ?? 0;
            $belumSelesaiByJenis[$jenis] = $count - $done;
        }

        $rtIds = Warga::query()
            ->when($onlyRtId, fn ($q) => $q->where('no_rt_id', $onlyRtId))
            ->select('no_rt_id')
            ->distinct()
            ->orderBy('no_rt_id')
            ->pluck('no_rt_id')
            ->toArray();

        $groups = [];
        foreach ($rtIds as $rtId) {
            $query = SuratKetWarga::query()->with(['warga', 'jenisSurat', 'status'])
                ->whereHas('warga', fn ($q) => $q->where('no_rt_id', $rtId))
                ->orderByDesc('tgl_pengajuan');

            if ($startDate && $endDate) {
                $query->whereBetween('tgl_pengajuan', [$startDate, $endDate]);
            }

            $rows = $query->get()->map(function (SuratKetWarga $s) {
                return [
                    'id' => $s->surat_ket_warga_id,
                    'warga' => $s->warga?->nama,
                    'jenis' => $s->jenisSurat?->nama_surat,
                    'tgl_pengajuan' => $s->tgl_pengajuan,
                    'tgl_selesai' => $s->tgl_selesai,
                    'status' => $s->status?->keterangan,
                ];
            })->toArray();

            $groups[] = [
                'rt_label' => $rtId,
                'rt_id' => $rtId,
                'rows' => $rows,
            ];
        }

        return [
            'summary' => [
                'total' => $totalSurat,
                'selesai' => $suratSelesai,
                'belum_selesai' => $suratBelumSelesai,
                'detail' => [
                    'total' => $totalByJenis,
                    'selesai' => $selesaiByJenis,
                    'belum_selesai' => $belumSelesaiByJenis,
                ],
            ],
            'groups' => $groups,
        ];
    }
}
