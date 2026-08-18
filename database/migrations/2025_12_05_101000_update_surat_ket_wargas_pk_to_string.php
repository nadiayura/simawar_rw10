<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('surat_ket_wargas')) {
            return;
        }

        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_ket_wargas', 'surat_ket_warga_id')) {
                $table->string('surat_ket_warga_id', 64)->nullable()->after('id');
            }
        });

        $rows = DB::table('surat_ket_wargas')->orderBy('tgl_pengajuan')->orderBy('id')->get(['id', 'tgl_pengajuan']);
        $seqByDate = [];
        foreach ($rows as $row) {
            $d = $row->tgl_pengajuan ? \Carbon\Carbon::parse($row->tgl_pengajuan) : now();
            $key = $d->format('Y-m-d');
            $seqByDate[$key] = ($seqByDate[$key] ?? 0) + 1;
            $num = $seqByDate[$key];
            $dateStr = $d->format('dmY');
            $newId = 'SUKET-WRG-'.str_pad((string) $num, 3, '0', STR_PAD_LEFT).'-'.$dateStr;
            DB::table('surat_ket_wargas')->where('id', $row->id)->update(['surat_ket_warga_id' => $newId]);
        }

        try {
            DB::statement('ALTER TABLE `surat_ket_wargas` MODIFY `id` BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE `surat_ket_wargas` DROP PRIMARY KEY');
        } catch (\Throwable $e) {
        }

        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (Schema::hasColumn('surat_ket_wargas', 'id')) {
                $table->dropColumn('id');
            }
        });

        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (Schema::hasColumn('surat_ket_wargas', 'surat_ket_warga_id')) {
                $table->primary('surat_ket_warga_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('surat_ket_wargas')) {
            return;
        }
        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_ket_wargas', 'id')) {
                $table->unsignedBigInteger('id')->nullable()->first();
            }
        });
    }
};
