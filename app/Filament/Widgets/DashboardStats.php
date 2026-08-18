<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Pengaduans\PengaduanResource;
use App\Filament\Resources\RekapKeuangans\RekapKeuanganResource;
use App\Filament\Resources\SuratKetWargas\SuratKetWargaResource;
use App\Filament\Resources\TagihanIuranWargas\TagihanIuranWargaResource;
use App\Filament\Resources\Wargas\WargaResource;
use App\Models\PembayaranMidtrans;
use App\Models\Pengaduan;
use App\Models\Status;
use App\Models\SuratKetWarga;
use App\Models\TagihanIuranWarga;
use App\Models\Warga;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardStats extends BaseWidget
{
    protected string $view = 'filament.widgets.dashboard-stats';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();
        $isRt = $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id;
        $rtId = $isRt ? $user->warga->no_rt_id : null;

        $wargaCount = Warga::query()
            ->when($isRt, fn ($q) => $q->where('no_rt_id', $rtId))
            ->count();

        $wargaMenungguVerifikasi = Warga::query()
            ->whereHas('user', function ($query) {
                $query->whereHas('role', function ($query) {
                    $query->where('name', 'tamu');
                });
            })
            ->when($isRt, fn ($q) => $q->where('no_rt_id', $rtId))
            ->count();

        $pengaduanBaru = Pengaduan::whereHas('status', function ($q) {
            $q->whereRaw('LOWER(keterangan) = ?', ['pending']);
        })
            ->when($isRt, fn ($q) => $q->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId)))
            ->whereMonth('tgl_pengajuan', now()->month)
            ->whereYear('tgl_pengajuan', now()->year)
            ->count();

        $suratBaseQuery = SuratKetWarga::query()
            ->when($isRt, fn ($q) => $q->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId)));

        $suratTotal = (clone $suratBaseQuery)->count();

        $selesaiStatusId = Status::idForFitur('surat', 'selesai')
            ?? Status::idForFitur('surat_ket_warga', 'selesai')
            ?? Status::idByName('selesai');

        $suratSelesaiQuery = (clone $suratBaseQuery)->when(
            $selesaiStatusId,
            fn ($q) => $q->where('status_id', $selesaiStatusId),
            fn ($q) => $q->whereHas('status', function ($s) {
                $s->whereRaw('LOWER(keterangan) = ?', ['selesai']);
            })
        );

        $suratSelesai = (clone $suratSelesaiQuery)->count();
        $suratMenunggu = $suratTotal - $suratSelesai;

        $iuranLunas = TagihanIuranWarga::whereHas('status', function ($q) {
            $q->whereRaw('LOWER(keterangan) = ?', ['lunas']);
        })
            ->when($isRt, fn ($q) => $q->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId)))
            ->count();

        $iuranBelum = TagihanIuranWarga::whereHas('status', function ($q) {
            $q->whereRaw('LOWER(keterangan) = ?', ['belum bayar']);
        })
            ->when($isRt, fn ($q) => $q->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId)))
            ->count();

        $kasThis = PembayaranMidtrans::query()
            ->where('status_id', Status::idForFitur('keuangan', 'settlement'))
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->when($isRt, function ($q) use ($rtId) {
                $q->whereIn('PembayaranMidtrans_id', TagihanIuranWarga::query()
                    ->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId))
                    ->select('PembayaranMidtrans_id'));
            })
            ->sum('jumlah');

        $kasPrev = PembayaranMidtrans::query()
            ->where('status_id', Status::idForFitur('keuangan', 'settlement'))
            ->whereMonth('updated_at', now()->subMonth()->month)
            ->whereYear('updated_at', now()->subMonth()->year)
            ->when($isRt, function ($q) use ($rtId) {
                $q->whereIn('PembayaranMidtrans_id', TagihanIuranWarga::query()
                    ->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId))
                    ->select('PembayaranMidtrans_id'));
            })
            ->sum('jumlah');
        $kasDelta = $kasPrev > 0 ? round((($kasThis - $kasPrev) / $kasPrev) * 100) : 0;

        return [
            Stat::make('Warga Aktif', (string) $wargaCount)
                ->url(WargaResource::getUrl('index')),
            Stat::make('Pengaduan Baru', (string) $pengaduanBaru)
                ->url(PengaduanResource::getUrl('index')),
            Stat::make('Surat Menunggu', (string) $suratMenunggu)
                ->url(SuratKetWargaResource::getUrl('index')),
            Stat::make('Iuran Bulan Ini', $iuranLunas.'/'.$iuranBelum)
                ->url(TagihanIuranWargaResource::getUrl('index')),
            Stat::make('Arus Kas RT', 'Rp '.number_format($kasThis, 0, ',', '.'))
                ->description(($kasDelta >= 0 ? '+' : '').$kasDelta.'%')
                ->url(RekapKeuanganResource::getUrl('index')),
        ];
    }

    protected function getSuratNewCount(): int
    {
        $user = Auth::user();
        $isRt = $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id;
        $rtId = $isRt ? $user->warga->no_rt_id : null;

        $base = SuratKetWarga::query()
            ->when($isRt, fn ($q) => $q->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId)))
            ->whereDate('created_at', now()->toDateString());

        $selesaiStatusId = Status::idForFitur('surat', 'selesai')
            ?? Status::idForFitur('surat_ket_warga', 'selesai')
            ?? Status::idByName('selesai');

        $base->when(
            $selesaiStatusId,
            fn ($q) => $q->where('status_id', '!=', $selesaiStatusId),
            fn ($q) => $q->where(function ($qq) {
                $qq->whereNull('status_id')
                    ->orWhereHas('status', function ($s) {
                        $s->whereRaw('LOWER(keterangan) <> ?', ['selesai']);
                    });
            })
        );

        return $base->count();
    }

    protected function getIuranNewCount(): int
    {
        $user = Auth::user();
        $isRt = $user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id;
        $rtId = $isRt ? $user->warga->no_rt_id : null;

        return TagihanIuranWarga::whereHas('status', function ($q) {
            $q->whereRaw('LOWER(keterangan) = ?', ['lunas']);
        })
            ->when($isRt, fn ($q) => $q->whereHas('warga', fn ($w) => $w->where('no_rt_id', $rtId)))
            ->whereDate('updated_at', now()->toDateString())
            ->count();
    }
}
