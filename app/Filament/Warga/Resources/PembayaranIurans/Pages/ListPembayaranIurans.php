<?php

namespace App\Filament\Warga\Resources\PembayaranIurans\Pages;

use App\Filament\Warga\Resources\PembayaranIurans\PembayaranIuranResource;
use App\Models\Status;
use App\Models\TagihanIuranWarga;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ListPembayaranIurans extends Page
{
    protected static string $resource = PembayaranIuranResource::class;

    protected string $view = 'filament.warga.pembayaran-iurans.pages.list-pembayaran-iurans';

    public function getHeading(): string
    {
        return 'Daftar Pembayaran Iuran Warga';
    }

    public function getTitle(): string
    {
        return 'Daftar Pembayaran Iuran Warga';
    }

    public function getBreadcrumb(): string
    {
        return 'Pembayaran Iuran';
    }

    public function getBreadcrumbs(): array
    {
        return [
            PembayaranIuranResource::getUrl('index') => 'Pembayaran Iuran',
        ];
    }

    protected function getViewData(): array
    {
        $user = Auth::user();
        $wargaNik = $user?->warga_nik;

        if (! $wargaNik) {
            return [
                'years' => [],
            ];
        }

        $records = TagihanIuranWarga::query()
            ->with(['periode', 'status', 'pembayaranTunai'])
            ->where('warga_nik', $wargaNik)
            ->leftJoin('periode_iurans', 'periode_iurans.periode_iuran_id', '=', 'tagihan_iuran_wargas.periode_iuran_id')
            ->select('tagihan_iuran_wargas.*')
            ->selectRaw('periode_iurans.tahun as periode_tahun, periode_iurans.bulan as periode_bulan')
            ->orderByDesc('periode_iurans.tahun')
            ->orderBy('periode_iurans.bulan')
            ->get();

        $unpaidStatusId = Status::idForFitur('keuangan', 'Belum bayar');

        $earliestUnpaidKey = [];
        foreach ($records as $t) {
            $tahun = (int) ($t->periode?->tahun ?? $t->periode_tahun ?? 0);
            $bulan = (int) ($t->periode?->bulan ?? $t->periode_bulan ?? 0);
            $iuranId = (string) ($t->iuran_id ?? '');

            if (! $tahun || ! $bulan || $iuranId === '') {
                continue;
            }

            $statusId = $t->status_id;
            $statusName = strtolower((string) ($t->status?->keterangan ?? ''));
            $isPaid = in_array($statusName, ['lunas', 'settlement'], true) || (bool) $t->PembayaranTunai_id;
            $isUnpaid = ($unpaidStatusId && (string) $statusId === (string) $unpaidStatusId) || $statusName === 'belum bayar';

            if ($isPaid || ! $isUnpaid) {
                continue;
            }

            $key = $tahun.'|'.$iuranId;
            $current = $earliestUnpaidKey[$key] ?? null;
            if ($current === null || $bulan < $current['bulan']) {
                $earliestUnpaidKey[$key] = [
                    'bulan' => $bulan,
                    'tagihan' => (string) $t->getKey(),
                ];
            }
        }

        $years = [];
        foreach ($records as $t) {
            $tahun = (int) ($t->periode?->tahun ?? $t->periode_tahun ?? 0);
            $bulan = (int) ($t->periode?->bulan ?? $t->periode_bulan ?? 0);

            if (! $tahun || ! $bulan) {
                continue;
            }

            $statusLabel = $t->status?->keterangan;
            if ($t->pembayaranTunai && ($t->pembayaranTunai->status_id === Status::idForFitur('keuangan', 'settlement'))) {
                $statusLabel = 'settlement';
            }

            $statusName = strtolower((string) $statusLabel);
            $isPaid = in_array($statusName, ['lunas', 'settlement'], true) || (bool) $t->PembayaranTunai_id;

            $iuranId = (string) ($t->iuran_id ?? '');
            $key = $tahun.'|'.$iuranId;
            $earliestForKey = $earliestUnpaidKey[$key]['tagihan'] ?? null;
            $canPay = ! $isPaid && $earliestForKey && (string) $t->getKey() === (string) $earliestForKey;

            if (! isset($years[$tahun])) {
                $years[$tahun] = [
                    'year' => $tahun,
                    'rows' => [],
                ];
            }

            $years[$tahun]['rows'][] = [
                'id' => (string) $t->getKey(),
                'bulan' => $t->periode?->nama_bulan ?? (string) $bulan,
                'nominal' => $t->nominal_tagihan,
                'status' => $statusLabel,
                'tanggal_lunas' => $t->tanggal_lunas,
                'can_pay' => (bool) $canPay,
                'has_tunai' => (bool) $t->PembayaranTunai_id,
                'bukti_tunai' => is_array($t->pembayaranTunai?->bukti) ? $t->pembayaranTunai->bukti : [],
                'penerima_tunai' => (string) ($t->pembayaranTunai?->penerima ?? ''),
            ];
        }

        krsort($years);

        return [
            'years' => array_values($years),
        ];
    }
}
