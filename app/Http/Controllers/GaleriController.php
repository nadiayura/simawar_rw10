<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;

class GaleriController extends Controller
{
    public function index(Request $request)
    {
        $query = Kegiatan::query();
        
        // Filter berdasarkan jenis kegiatan jika ada
        if ($request->filled('jenis')) {
            $query->where('jenis_kegiatan', $request->jenis);
        }
        
        // Filter berdasarkan pencarian nama kegiatan
        if ($request->filled('search')) {
            $query->where('nama_kegiatan', 'like', '%' . $request->search . '%');
        }
        
        // Urutkan berdasarkan tanggal terbaru
        $kegiatans = $query->orderBy('tanggal', 'desc')->paginate(12);
        
        // Ambil semua jenis kegiatan untuk filter
        $jenisKegiatan = Kegiatan::select('jenis_kegiatan')
            ->distinct()
            ->pluck('jenis_kegiatan')
            ->filter()
            ->values();
        
        return view('galeri', compact('kegiatans', 'jenisKegiatan'));
    }
}