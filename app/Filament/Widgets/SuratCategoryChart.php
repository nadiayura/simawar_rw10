<?php

namespace App\Filament\Widgets;

use App\Models\SuratKetWarga;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SuratCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Kategori Surat';

    protected int|string|array $columnSpan = 1;

    protected function getMinHeight(): ?string
    {
        return '320px';
    }

    protected function getMaxHeight(): ?string
    {
        return '320px';
    }

    protected function getContentHeight(): ?string
    {
        return '220px';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getTotalSurat(): int
    {
        $user = Auth::user();
        $isRt = $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id;
        $rtId = $isRt ? $user->warga->no_rt_id : null;

        return SuratKetWarga::query()
            ->when($isRt, fn ($q) => $q->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId)))
            ->count();
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $isRt = $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id;
        $rtId = $isRt ? $user->warga->no_rt_id : null;

        $baseQuery = SuratKetWarga::query()
            ->when($isRt, fn ($q) => $q->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId)))
            ->with('jenisSurat');

        $counts = $baseQuery->get()
            ->groupBy(fn (SuratKetWarga $s) => $s->jenisSurat?->nama_surat ?? 'Lainnya')
            ->map->count()
            ->sortDesc();

        $top = $counts->take(4);
        $rawLabels = $top->keys()->values()->all();
        $data = $top->values()->all();

        $labels = [];
        foreach ($rawLabels as $index => $name) {
            $count = $data[$index] ?? 0;
            $labels[] = $name.' ('.$count.')';
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => ['#2ea7d6', '#5bc0e0', '#9adcf0', '#cfeef8'],
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,

            'layout' => [
                'padding' => 0,
            ],

            'radius' => '85%',
            'cutout' => '60%',

            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 10,
                        'padding' => 6,
                    ],
                ],
            ],
        ];
    }
}
