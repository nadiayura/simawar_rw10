<?php

namespace App\Filament\Resources\RekapKeuangans\Pages;

use App\Filament\Resources\RekapKeuangans\RekapKeuanganResource;
use App\Models\NoRt;
use App\Models\RekapKeuangan;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;

class ReportRekapKeuangan extends Page
{
    protected static string $resource = RekapKeuanganResource::class;

    protected static ?string $pluralModelLabel = 'Unduh Rekap Keuangan';

    protected string $view = 'filament.resources.rekap-keuangans.pages.report-rekap-keuangan';

    public function getHeading(): string
    {
        return 'Unduh Rekap Keuangan';
    }

    public function getTitle(): string
    {
        return 'Unduh Rekap Keuangan';
    }

    protected function getViewData(): array
    {
        $start = request()->query('start');
        $end = request()->query('end');
        $category = request()->query('category');
        $noRtId = request()->query('no_rt_id');
        $user = request()->user();
        if ($user && $user->role && $user->role->isRT()) {
            $noRtId = optional($user->warga)->no_rt_id;
        }
        $show = request()->query('show') === '1';
        $initialBalance = (float) (request()->query('initial_balance', 0));

        $startDate = null;
        $endDate = null;
        if ($start && $end) {
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $end)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = null;
                $endDate = null;
            }
        }

        $categories = RekapKeuangan::query()
            ->select('sumber')
            ->distinct()
            ->orderBy('sumber')
            ->pluck('sumber')
            ->filter()
            ->values()
            ->all();
        $rtQuery = NoRt::query()->orderBy('nomor');
        if ($user && $user->role && $user->role->isRT() && optional($user->warga)->no_rt_id) {
            $rtQuery->where('no_rt_id', optional($user->warga)->no_rt_id);
        }
        $rtOptions = $rtQuery->pluck('nomor', 'no_rt_id')->toArray();

        $query = RekapKeuangan::query()->whereIn('jenis_trans', ['masuk', 'keluar']);
        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }
        // if ($category) {
        //     $query->where(function ($q) use ($category) {
        //         $q->where('jenis_trans', 'keluar')
        //           ->orWhere(function ($qq) use ($category) {
        //               $qq->where('jenis_trans', 'masuk')
        //                  ->where('sumber', $category);
        //           });
        //     });
        // }
        if ($noRtId) {
            $query->where(function ($q) use ($noRtId) {
                $q->whereHas('tagihan.warga', function ($w) use ($noRtId) {
                    $w->where('no_rt_id', $noRtId);
                })
                    ->orWhere(function ($qq) use ($noRtId) {
                        $qq->whereNull('tagihan_iuran_id')
                            ->where('no_rt_id', $noRtId);
                    });
            });
        }
        $rows = $query->orderBy('tanggal')->get(['tanggal', 'jenis_trans', 'sumber', 'nominal', 'metode']);

        $totalMasuk = $rows->filter(fn ($r) => strtolower((string) $r->jenis) === 'masuk')
            ->sum('nominal');
        $totalKeluar = $rows->filter(fn ($r) => strtolower((string) $r->jenis) === 'keluar')
            ->sum('nominal');

        return [
            'start' => $start,
            'end' => $end,
            'category' => $category,
            'no_rt_id' => $noRtId,
            'rtOptions' => $rtOptions,
            'show' => $show,
            'download_date' => $show ? now()->format('Y-m-d') : null,
            'initial_balance' => $initialBalance,
            'rows' => $rows,
            'total_masuk' => (float) $totalMasuk,
            'total_keluar' => (float) $totalKeluar,
        ];
    }
}
