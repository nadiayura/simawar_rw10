<?php

namespace App\Filament\Resources\Pengaduans\Pages;

use App\Filament\Resources\Pengaduans\PengaduanResource;
use App\Models\Pengaduan;
use App\Models\Status;
use App\Models\Warga;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class GroupedPengaduans extends Page
{
    protected static string $resource = PengaduanResource::class;

    protected static string $pluralLabel = 'Pengaduan Warga';

    protected string $view = 'filament.resources.pengaduans.pages.grouped-pengaduans';

    public function getHeading(): string
    {
        return 'Pengaduan Warga';
    }

    public function getTitle(): string
    {
        return 'Pengaduan Warga';
    }

    public function getBreadcrumb(): string
    {
        return 'Pengaduan Warga';
    }

    public function getBreadcrumbs(): array
    {
        return [
            PengaduanResource::getUrl('index') => 'Pengaduan Warga',
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

        $summaryBaseQuery = Pengaduan::query()
            ->when($onlyRtId, function ($q) use ($onlyRtId) {
                $q->whereHas('warga', function ($w) use ($onlyRtId) {
                    $w->where('no_rt_id', $onlyRtId);
                });
            });

        if ($startDate && $endDate) {
            $summaryBaseQuery->whereBetween('tgl_pengajuan', [$startDate, $endDate]);
        }

        $totalPengaduan = (clone $summaryBaseQuery)->count();

        $selesaiStatusId = Status::idForFitur('pengaduan', 'selesai')
            ?? Status::idForFitur('pengaduan', 'Selesai');

        $selesaiQuery = (clone $summaryBaseQuery)
            ->when(
                $selesaiStatusId,
                fn ($q) => $q->where('status_id', $selesaiStatusId),
                fn ($q) => $q->whereHas('status', function ($s) {
                    $s->whereRaw('LOWER(keterangan) = ?', ['selesai']);
                })
            );

        $pengaduanSelesai = (clone $selesaiQuery)->count();

        $pengaduanBelumSelesai = $totalPengaduan - $pengaduanSelesai;

        $totalByJenis = (clone $summaryBaseQuery)
            ->with('jenisPengaduan')
            ->get()
            ->groupBy(fn (Pengaduan $p) => strtolower((string) ($p->jenisPengaduan?->nama ?? 'Lainnya')))
            ->map->count()
            ->toArray();

        $selesaiByJenis = (clone $selesaiQuery)
            ->with('jenisPengaduan')
            ->get()
            ->groupBy(fn (Pengaduan $p) => strtolower((string) ($p->jenisPengaduan?->nama ?? 'Lainnya')))
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
            $query = Pengaduan::query()->with(['warga', 'jenisPengaduan', 'status'])
                ->whereHas('warga', fn ($q) => $q->where('no_rt_id', $rtId))
                ->orderByDesc('tgl_pengajuan');

            if ($startDate && $endDate) {
                $query->whereBetween('tgl_pengajuan', [$startDate, $endDate]);
            }

            $rows = $query->get()->map(function (Pengaduan $p) {
                return [
                    'id' => $p->pengaduan_id,
                    'warga' => $p->warga?->nama,
                    'jenis' => $p->jenisPengaduan?->nama,
                    'judul' => $p->jdl_pengaduan,
                    'tgl_pengajuan' => $p->tgl_pengajuan,
                    'status' => $p->status?->keterangan,
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
                'total' => $totalPengaduan,
                'selesai' => $pengaduanSelesai,
                'belum_selesai' => $pengaduanBelumSelesai,
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
