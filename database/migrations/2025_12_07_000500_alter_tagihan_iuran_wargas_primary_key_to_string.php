<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas')) {
            return;
        }

        if (! Schema::hasColumn('tagihan_iuran_wargas', 'tagihan_iuran_id')) {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
                $table->string('tagihan_iuran_id', 150)->nullable()->after('id');
            });
        }

        $rows = DB::table('tagihan_iuran_wargas')->orderBy('id')->get(['id', 'warga_nik', 'periode_id']);
        $counters = [];
        foreach ($rows as $row) {
            $periode = DB::table('periode_iurans')->where('periode_iuran_id', $row->periode_id)->first();
            $mm = isset($periode->bulan) ? str_pad((string) ((int) $periode->bulan), 2, '0', STR_PAD_LEFT) : '00';
            $yyyy = isset($periode->tahun) ? (string) ((int) $periode->tahun) : date('Y');
            $key = $mm.$yyyy.'-'.$row->warga_nik;
            $counters[$key] = ($counters[$key] ?? 0) + 1;
            $seq = str_pad((string) $counters[$key], 3, '0', STR_PAD_LEFT);
            $newId = 'TGH-IURAN-'.$key.'('.$seq.')';
            DB::table('tagihan_iuran_wargas')->where('id', $row->id)->update(['tagihan_iuran_id' => $newId]);
        }

        if (Schema::hasTable('pembayaran_midtrans')) {
            try {
                $fk = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->whereRaw('TABLE_SCHEMA = DATABASE()')
                    ->where('TABLE_NAME', 'pembayaran_midtrans')
                    ->where('COLUMN_NAME', 'tagihan_iuran_id')
                    ->value('CONSTRAINT_NAME');
                if ($fk) {
                    DB::statement('ALTER TABLE `pembayaran_midtrans` DROP FOREIGN KEY `'.$fk.'`');
                }
            } catch (\Throwable $e) {
            }

            DB::statement('ALTER TABLE pembayaran_midtrans MODIFY tagihan_iuran_id VARCHAR(150) NULL');

            try {
                Schema::table('pembayaran_midtrans', function (Blueprint $table) {
                    $table->foreign('tagihan_iuran_id')->references('tagihan_iuran_id')->on('tagihan_iuran_wargas')->onDelete('cascade');
                });
            } catch (\Throwable $e) {
            }
        }

        try {
            DB::statement('ALTER TABLE tagihan_iuran_wargas DROP PRIMARY KEY');
        } catch (\Throwable $e) {
        }

        try {
            DB::statement('ALTER TABLE tagihan_iuran_wargas ADD PRIMARY KEY (`tagihan_iuran_id`)');
        } catch (\Throwable $e) {
        }

        if (Schema::hasColumn('tagihan_iuran_wargas', 'id')) {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tagihan_iuran_wargas') && ! Schema::hasColumn('tagihan_iuran_wargas', 'id')) {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
                $table->bigIncrements('id')->first();
            });
        }
    }
};
