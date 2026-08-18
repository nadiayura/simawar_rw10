<?php

namespace App\Http\Controllers;

use App\Models\KegKarangTaruna;
use Illuminate\Http\Request;

class KegKarangTarunaController extends Controller
{
    public function index(Request $request)
    {
        $query = KegKarangTaruna::query();
        $query->whereHas('status', function ($q) {
            $q->where('keterangan', '!=', 'Dijadwalkan');
        });
        if ($request->filled('search')) {
            $query->where('nama_kegiatan', 'like', '%'.$request->search.'%');
        }
        $kegiatans = $query->orderBy('tanggal', 'desc')->paginate(12);
        $jenisKegiatan = collect(['Karang Taruna']);
        $groupedKegiatans = $kegiatans->getCollection()
            ->groupBy(function ($item) {
                return optional($item->tanggal)->format('Y-m');
            })
            ->sortKeysDesc();
        $monthLabels = [];
        foreach ($groupedKegiatans as $key => $items) {
            $monthLabels[$key] = optional($items->first()->tanggal)->translatedFormat('F Y');
        }

        return view('galeri', compact('kegiatans', 'jenisKegiatan', 'groupedKegiatans', 'monthLabels'));
    }

    public function show($keg_karang_taruna_id)
    {
        $kegiatan = KegKarangTaruna::query()
            ->with(['pjWarga', 'status'])
            ->where('keg_karang_taruna_id', $keg_karang_taruna_id)
            ->whereHas('status', function ($q) {
                $q->where('keterangan', '!=', 'Dijadwalkan');
            })
            ->firstOrFail();

        return view('galeri-detail', compact('kegiatan'));
    }
}
