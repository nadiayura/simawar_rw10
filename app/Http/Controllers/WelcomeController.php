<?php

namespace App\Http\Controllers;

use App\Models\KegKarangTaruna;
use App\Models\KegKesehatan;
use App\Models\KetuaRt;
use App\Models\NoRt;
use App\Models\Pengaduan;
use App\Models\Status;
use App\Models\Struktural;
use App\Models\SuratKetWarga;
use App\Models\Warga;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        $strukturals = Struktural::with('warga')->ordered()->active()->get();

        $ketuaRts = KetuaRt::with('warga')->where('is_active', true)->get();

        $rwStructure = [
            'ketua' => $strukturals->filter(fn ($s) => str_contains($s->jabatan, 'Ketua RW'))->first(),
            'sekretaris' => $strukturals->filter(fn ($s) => str_contains($s->jabatan, 'Sekretaris RW'))->first(),
            'bendahara' => $strukturals->filter(fn ($s) => str_contains($s->jabatan, 'Bendahara RW'))->first(),
        ];

        $rtStructures = [];

        foreach ($ketuaRts as $ketuaRt) {
            $noRtId = $ketuaRt->no_rt_id ?? optional($ketuaRt->warga)->no_rt_id;
            $rtNumber = null;

            if ($noRtId) {
                $rtNumber = optional(NoRt::find($noRtId))->nomor;
            } elseif (! empty($ketuaRt->no_rt)) {
                $rtNumber = $ketuaRt->no_rt;
            }

            if (! $rtNumber) {
                continue;
            }

            if (! isset($rtStructures[$rtNumber])) {
                $rtStructures[$rtNumber] = [
                    'ketua' => null,
                    'sekretaris' => null,
                    'bendahara' => null,
                ];
            }

            $personData = (object) [
                'nama' => $ketuaRt->warga->nama ?? 'Nama tidak tersedia',
                'jabatan' => $ketuaRt->jabatan === 'Ketua RT' ? "Ketua RT {$rtNumber}" : $ketuaRt->jabatan,
                'no_rt' => $rtNumber,
                'periode_mulai' => $ketuaRt->periode_mulai ?: null,
                'periode_selesai' => $ketuaRt->periode_selesai ?: null,
                'foto' => $ketuaRt->warga->foto ?? null,
                'no_hp' => $ketuaRt->no_hp ?? $ketuaRt->warga->no_hp ?? null,
                'deskripsi' => "{$ketuaRt->jabatan} {$rtNumber} yang terdaftar dalam sistem",
                'is_active' => $ketuaRt->is_active,
            ];

            switch ($ketuaRt->jabatan) {
                case 'Ketua RT':
                    $rtStructures[$rtNumber]['ketua'] = $personData;
                    break;
                case 'Sekretaris RT':
                    $rtStructures[$rtNumber]['sekretaris'] = $personData;
                    break;
                case 'Bendahara RT':
                    $rtStructures[$rtNumber]['bendahara'] = $personData;
                    break;
            }
        }

        $strukturalRtData = $strukturals->filter(fn ($item) => str_contains(strtolower($item->jabatan), 'rt'));

        foreach ($strukturalRtData as $strukturalRt) {
            $rtNumber = null;

            // Prioritas: RT dari warga yang di-link ke struktural
            if ($strukturalRt->warga && $strukturalRt->warga->no_rt_id) {
                $rtNumber = optional(NoRt::find($strukturalRt->warga->no_rt_id))->nomor;
            }

            if (! $rtNumber && ! empty($strukturalRt->no_rt)) {
                $rtNumber = $strukturalRt->no_rt;
            }

            if (! $rtNumber) {
                continue;
            }

            if (! isset($rtStructures[$rtNumber])) {
                $rtStructures[$rtNumber] = [
                    'ketua' => null,
                    'sekretaris' => null,
                    'bendahara' => null,
                ];
            }

            if (str_contains(strtolower($strukturalRt->jabatan), 'ketua rt') && ! $rtStructures[$rtNumber]['ketua']) {
                $rtStructures[$rtNumber]['ketua'] = $strukturalRt;
            } elseif (str_contains(strtolower($strukturalRt->jabatan), 'sekretaris') && ! $rtStructures[$rtNumber]['sekretaris']) {
                $rtStructures[$rtNumber]['sekretaris'] = $strukturalRt;
            } elseif (str_contains(strtolower($strukturalRt->jabatan), 'bendahara') && ! $rtStructures[$rtNumber]['bendahara']) {
                $rtStructures[$rtNumber]['bendahara'] = $strukturalRt;
            }
        }

        uksort($rtStructures, fn ($a, $b) => strnatcmp($a, $b));

        $galeriKatar = KegKarangTaruna::query()
            ->whereHas('status', function ($q) {
                $q->where('keterangan', '!=', 'Dijadwalkan');
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        $beritaKesehatan = KegKesehatan::query()
            ->with('status')
            ->whereHas('status', function ($q) {
                $q->whereRaw('LOWER(keterangan) = ?', ['selesai']);
            })
            ->orderBy('tgl', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->filter(fn ($k) => is_array($k->dokumentasi) && count($k->dokumentasi) > 0)
            ->take(3);

        $rtCount = NoRt::query()->count();
        $wargaCount = Warga::query()->count();

        $pengaduanSelesaiStatusId = Status::idForFitur('pengaduan', 'selesai')
            ?? Status::idForFitur('pengaduan', 'Selesai');
        $pengaduanSelesaiCount = Pengaduan::query()
            ->when(
                $pengaduanSelesaiStatusId,
                fn ($q) => $q->where('status_id', $pengaduanSelesaiStatusId),
                fn ($q) => $q->whereHas('status', function ($s) {
                    $s->whereRaw('LOWER(keterangan) = ?', ['selesai']);
                })
            )
            ->count();

        $suratSelesaiStatusId = Status::idForFitur('surat', 'selesai')
            ?? Status::idForFitur('surat_ket_warga', 'selesai')
            ?? Status::idByName('selesai');
        $suratSelesaiCount = SuratKetWarga::query()
            ->when(
                $suratSelesaiStatusId,
                fn ($q) => $q->where('status_id', $suratSelesaiStatusId),
                fn ($q) => $q->whereHas('status', function ($s) {
                    $s->whereRaw('LOWER(keterangan) = ?', ['selesai']);
                })
            )
            ->count();

        return view('welcome', compact(
            'rwStructure',
            'rtStructures',
            'galeriKatar',
            'beritaKesehatan',
            'rtCount',
            'wargaCount',
            'pengaduanSelesaiCount',
            'suratSelesaiCount'
        ));
    }

    public function showBeritaKesehatan($keg_kesehatan_id)
    {
        $berita = KegKesehatan::query()->where('keg_kesehatan_id', $keg_kesehatan_id)->firstOrFail();

        return view('berita-kesehatan-detail', compact('berita'));
    }

    public function kegiatanKesehatan(Request $request)
    {
        $query = KegKesehatan::query();

        if ($request->has('search') && ! empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_kegiatan', 'like', '%'.$searchTerm.'%')
                    ->orWhere('aktivitas_dilakukan', 'like', '%'.$searchTerm.'%')
                    ->orWhere('penanggung_jawab', 'like', '%'.$searchTerm.'%')
                    ->orWhere('jenis_kegiatan', 'like', '%'.$searchTerm.'%');
            });
        }

        if ($request->has('filter') && $request->filter !== 'all') {
            $query->where('jenis_kegiatan', 'like', '%'.$request->filter.'%');
        }

        $kegiatanKesehatan = $query->orderBy('tgl', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        $groupedKegiatanKesehatan = $kegiatanKesehatan->getCollection()
            ->groupBy(function ($item) {
                return optional($item->tgl)->format('Y-m');
            })
            ->sortKeysDesc();
        $monthLabelsKegiatan = [];
        foreach ($groupedKegiatanKesehatan as $key => $items) {
            $monthLabelsKegiatan[$key] = optional($items->first()->tgl)->translatedFormat('F Y');
        }

        return view('kegiatan-kesehatan', compact('kegiatanKesehatan', 'groupedKegiatanKesehatan', 'monthLabelsKegiatan'));
    }
}
