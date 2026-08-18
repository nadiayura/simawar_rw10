<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }

        $statuses = DB::table('statuses')
            ->orderBy('fitur')
            ->orderBy('keterangan')
            ->get(['status_id']);

        if ($statuses->isEmpty()) {
            return;
        }

        $seq = 0;
        $map = [];
        foreach ($statuses as $row) {
            $seq++;
            $map[$row->status_id] = 'STS-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
        }

        $tables = [
            'pengaduan',
            'surat_ket_wargas',
            'tagihan_iuran_wargas',
            'pembayaran_midtrans',
            'keg_kesehatans',
            'keg_karang_taruna',
        ];

        foreach ($tables as $tbl) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }
            try {
                $fk = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->whereRaw('TABLE_SCHEMA = DATABASE()')
                    ->where('TABLE_NAME', $tbl)
                    ->where('COLUMN_NAME', 'status_id')
                    ->whereNotNull('REFERENCED_TABLE_NAME')
                    ->value('CONSTRAINT_NAME');
                if ($fk) {
                    DB::statement("ALTER TABLE `{$tbl}` DROP FOREIGN KEY `{$fk}`");
                }
            } catch (\Throwable $e) {
            }
        }

        foreach ($map as $old => $new) {
            DB::table('statuses')->where('status_id', $old)->update(['status_id' => $new]);
            foreach ($tables as $tbl) {
                if (! Schema::hasTable($tbl)) {
                    continue;
                }
                DB::table($tbl)->where('status_id', $old)->update(['status_id' => $new]);
            }
        }

        foreach ($tables as $tbl) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }
            try {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->foreign('status_id')->references('status_id')->on('statuses')->onDelete('restrict');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void {}
};
