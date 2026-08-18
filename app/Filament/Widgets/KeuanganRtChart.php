<?php

namespace App\Filament\Widgets;

use App\Models\RekapKeuangan;
use App\Models\TagihanIuranWarga;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class KeuanganRtChart extends ChartWidget
{
    protected ?string $heading = null;

    protected int|string|array $columnSpan = 1;

    protected function getMaxHeight(): ?string
    {
        return '320px';
    }

    protected function getMinHeight(): ?string
    {
        return '320px';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $isRt = $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id;
        $rtId = $isRt ? $user->warga->no_rt_id : null;

        $labels = [];
        $masukData = [];
        $keluarData = [];
        $years = [];

        $monthsWindow = 3;
        $start = now()->startOfMonth()->subMonths($monthsWindow - 1);
        $end = now()->endOfMonth();

        $jenisColumn = Schema::hasColumn('rekap_keuangan', 'jenis_trans') ? 'jenis_trans' : 'jenis';
        $jenisExpr = "LOWER(TRIM($jenisColumn))";

        $query = RekapKeuangan::query()
            ->selectRaw("YEAR(tanggal) as year, MONTH(tanggal) as month, $jenisExpr as jenis, SUM(nominal) as total")
            ->whereBetween('tanggal', [$start, $end])
            ->whereRaw("$jenisExpr IN ('masuk', 'keluar')")
            ->where(function ($q) use ($jenisExpr) {
                $q->whereNull('tagihan_iuran_id')
                    ->orWhereRaw("$jenisExpr = 'keluar'");
            });

        if ($rtId) {
            $query->where(function ($q) use ($rtId) {
                $q->whereHas('tagihan.warga', function ($w) use ($rtId) {
                    $w->where('no_rt_id', $rtId);
                })
                    ->orWhere(function ($qq) use ($rtId) {
                        $qq->whereNull('tagihan_iuran_id')
                            ->where('no_rt_id', $rtId);
                    });
            });
        }

        $raw = $query
            ->groupByRaw("YEAR(tanggal), MONTH(tanggal), $jenisExpr")
            ->get();

        $monthTotals = [];
        foreach ($raw as $row) {
            $key = sprintf('%04d-%02d', (int) $row->year, (int) $row->month);
            $jenis = (string) ($row->jenis ?? '');
            $monthTotals[$key][$jenis] = (float) ($row->total ?? 0);
        }

        $iuranQuery = TagihanIuranWarga::query()
            ->selectRaw('YEAR(tanggal_lunas) as year, MONTH(tanggal_lunas) as month, SUM(nominal_tagihan) as total')
            ->whereNotNull('tanggal_lunas')
            ->whereBetween('tanggal_lunas', [$start, $end]);

        if ($rtId) {
            $iuranQuery->whereHas('warga', function ($w) use ($rtId) {
                $w->where('no_rt_id', $rtId);
            });
        }

        $iuranRaw = $iuranQuery
            ->groupByRaw('YEAR(tanggal_lunas), MONTH(tanggal_lunas)')
            ->get();

        foreach ($iuranRaw as $row) {
            $key = sprintf('%04d-%02d', (int) $row->year, (int) $row->month);
            $monthTotals[$key]['masuk'] = (float) ($monthTotals[$key]['masuk'] ?? 0) + (float) ($row->total ?? 0);
        }

        $cursor = (clone $start);
        $monthDates = [];
        for ($i = 0; $i < $monthsWindow; $i++) {
            $monthDates[] = (clone $cursor);
            $cursor->addMonth();
        }

        $years = array_values(array_unique(array_map(fn ($d) => (int) $d->year, $monthDates)));
        $includeYearInLabel = count($years) > 1;

        foreach ($monthDates as $date) {
            $key = $date->format('Y-m');
            $labels[] = $includeYearInLabel ? $date->format('M y') : $date->format('M');
            $masukData[] = (float) ($monthTotals[$key]['masuk'] ?? 0);
            $keluarData[] = (float) ($monthTotals[$key]['keluar'] ?? 0);
        }

        $yearCaption = count($years) <= 1 ? (string) ($years[0] ?? now()->year) : (min($years).'–'.max($years));

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Masuk',
                    'data' => $masukData,
                    'borderColor' => '#2ea7d6',
                    'backgroundColor' => 'rgba(46,167,214,0.15)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Keluar',
                    'data' => $keluarData,
                    'borderColor' => '#9ca3af',
                    'backgroundColor' => 'rgba(156,163,175,0.15)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'yearCaption' => $yearCaption,
        ];
    }

    public function getHeading(): ?string
    {
        return 'Keuangan RT';
    }

    protected function getExtraAttributes(): array
    {
        return [
            'class' => 'h-full',
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'maintainAspectRatio' => true,
            'responsive' => true,
        ];
    }
}
