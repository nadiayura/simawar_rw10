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

        // Drop FK lama ke iurans.id
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'tagihan_iuran_wargas')
            ->where('COLUMN_NAME', 'iuran_id')
            ->where('REFERENCED_TABLE_NAME', 'iurans')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->get(['CONSTRAINT_NAME']);
        foreach ($refs as $ref) {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) use ($ref) {
                $table->dropForeign($ref->CONSTRAINT_NAME);
            });
        }

        // Tambah kolom baru string untuk menyimpan iuran_id string
        if (! Schema::hasColumn('tagihan_iuran_wargas', 'iuran_id_new')) {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
                $table->string('iuran_id_new', 32)->nullable()->after('warga_nik');
            });
        }

        // Backfill dari numeric ke string menggunakan mapping di iurans
        DB::statement('UPDATE tagihan_iuran_wargas t JOIN iurans i ON t.iuran_id = i.id SET t.iuran_id_new = i.iuran_id');

        // Ganti kolom lama
        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            $table->dropColumn('iuran_id');
        });
        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            $table->renameColumn('iuran_id_new', 'iuran_id');
        });

        // Tambah FK baru ke iurans.iuran_id (opsional)
        try {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
                $table->foreign('iuran_id')->references('iuran_id')->on('iurans')->onDelete('restrict');
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas')) {
            return;
        }
        // Drop FK baru jika ada
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'tagihan_iuran_wargas')
            ->where('COLUMN_NAME', 'iuran_id')
            ->where('REFERENCED_TABLE_NAME', 'iurans')
            ->where('REFERENCED_COLUMN_NAME', 'iuran_id')
            ->get(['CONSTRAINT_NAME']);
        foreach ($refs as $ref) {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) use ($ref) {
                $table->dropForeign($ref->CONSTRAINT_NAME);
            });
        }

        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            $table->unsignedBigInteger('iuran_id')->nullable()->after('warga_nik');
        });
    }
};
