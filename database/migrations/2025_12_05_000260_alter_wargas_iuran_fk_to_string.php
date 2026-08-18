<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wargas')) {
            return;
        }

        // Drop FK lama yang mungkin mereferensikan iurans.id
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'wargas')
            ->where('COLUMN_NAME', 'iuran_id')
            ->where('REFERENCED_TABLE_NAME', 'iurans')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->get(['CONSTRAINT_NAME']);
        foreach ($refs as $ref) {
            Schema::table('wargas', function (Blueprint $table) use ($ref) {
                $table->dropForeign($ref->CONSTRAINT_NAME);
            });
        }

        // Tambah kolom baru string untuk menyimpan iuran_id string
        if (! Schema::hasColumn('wargas', 'iuran_id_new')) {
            Schema::table('wargas', function (Blueprint $table) {
                $table->string('iuran_id_new', 32)->nullable();
            });
        }

        // Backfill dari numeric ke string menggunakan mapping di iurans
        if (Schema::hasColumn('wargas', 'iuran_id')) {
            DB::statement('UPDATE wargas w JOIN iurans i ON w.iuran_id = i.id SET w.iuran_id_new = i.iuran_id');
        }

        // Ganti kolom lama
        if (Schema::hasColumn('wargas', 'iuran_id')) {
            Schema::table('wargas', function (Blueprint $table) {
                $table->dropColumn('iuran_id');
            });
        }
        Schema::table('wargas', function (Blueprint $table) {
            $table->renameColumn('iuran_id_new', 'iuran_id');
        });

        // Tambah FK baru ke iurans.iuran_id (opsional, jika DB mendukung)
        try {
            Schema::table('wargas', function (Blueprint $table) {
                $table->foreign('iuran_id')->references('iuran_id')->on('iurans')->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('wargas')) {
            return;
        }

        // Drop FK baru jika ada
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'wargas')
            ->where('COLUMN_NAME', 'iuran_id')
            ->where('REFERENCED_TABLE_NAME', 'iurans')
            ->where('REFERENCED_COLUMN_NAME', 'iuran_id')
            ->get(['CONSTRAINT_NAME']);
        foreach ($refs as $ref) {
            Schema::table('wargas', function (Blueprint $table) use ($ref) {
                $table->dropForeign($ref->CONSTRAINT_NAME);
            });
        }

        // Kembalikan kolom numeric
        Schema::table('wargas', function (Blueprint $table) {
            $table->unsignedBigInteger('iuran_id')->nullable();
        });
    }
};
