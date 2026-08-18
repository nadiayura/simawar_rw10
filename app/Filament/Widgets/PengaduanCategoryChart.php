<?php

namespace App\Filament\Widgets;

use App\Models\Pengaduan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class PengaduanCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Kategori Pengaduan';

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

    public function getTotalPengaduan(): int
    {
        $user = Auth::user();
        $isRt = $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id;
        $rtId = $isRt ? $user->warga->no_rt_id : null;

        return Pengaduan::query()
            ->when($isRt, fn ($q) => $q->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId)))
            ->whereYear('tgl_pengajuan', now()->year)
            ->count();
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $isRt = $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id;
        $rtId = $isRt ? $user->warga->no_rt_id : null;

        $categories = ['keamanan', 'kebersihan', 'infrastruktur', 'lainnya'];
        $baseLabels = ['Keamanan', 'Kebersihan', 'Infrastruktur', 'Lainnya'];
        $data = [];
        $labels = [];

        foreach ($categories as $index => $cat) {
            $count = Pengaduan::whereHas('jenisPengaduan', function ($q) use ($cat) {
                $q->whereRaw('LOWER(nama) = ?', [$cat]);
            })
                ->when($isRt, fn ($q) => $q->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId)))
                ->whereYear('tgl_pengajuan', now()->year)
                ->count();

            $data[] = $count;
            $labels[] = ($baseLabels[$index] ?? $cat).' ('.$count.')';
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
