<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('surat_ket_wargas')) {
            DB::transaction(function () {
                $query = DB::table('surat_ket_wargas');
                if (Schema::hasColumn('surat_ket_wargas', 'tgl_pengajuan')) {
                    $query = $query->orderBy('tgl_pengajuan');
                }
                if (Schema::hasColumn('surat_ket_wargas', 'created_at')) {
                    $query = $query->orderBy('created_at');
                }
                $rows = $query->get(['surat_ket_warga_id', 'tgl_pengajuan', 'created_at']);
                $seq = 0;
                foreach ($rows as $row) {
                    $seq++;
                    $d = $row->tgl_pengajuan ? \Carbon\Carbon::parse($row->tgl_pengajuan) : ($row->created_at ? \Carbon\Carbon::parse($row->created_at) : now());
                    $dateStr = $d->format('dmY');
                    $newId = 'SUKET-WRG-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT).'-'.$dateStr;
                    DB::table('surat_ket_wargas')->where('surat_ket_warga_id', $row->surat_ket_warga_id)->update(['surat_ket_warga_id' => $newId]);
                }
            });
        }

        if (Schema::hasTable('pengaduan')) {
            DB::transaction(function () {
                $query = DB::table('pengaduan');
                if (Schema::hasColumn('pengaduan', 'tgl_pengajuan')) {
                    $query = $query->orderBy('tgl_pengajuan');
                }
                if (Schema::hasColumn('pengaduan', 'created_at')) {
                    $query = $query->orderBy('created_at');
                }
                $rows = $query->get(['pengaduan_id', 'tgl_pengajuan', 'created_at']);
                $seq = 0;
                foreach ($rows as $row) {
                    $seq++;
                    $d = $row->tgl_pengajuan ? \Carbon\Carbon::parse($row->tgl_pengajuan) : ($row->created_at ? \Carbon\Carbon::parse($row->created_at) : now());
                    $dateStr = $d->format('dmY');
                    $newId = 'ADU-WRG-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT).'-'.$dateStr;
                    DB::table('pengaduan')->where('pengaduan_id', $row->pengaduan_id)->update(['pengaduan_id' => $newId]);
                }
            });
        }
    }

    public function down(): void
    {
        // no-op
    }
};
