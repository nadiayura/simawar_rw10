<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_surats')) {
            return;
        }

        // 1) Tambah kolom baru string untuk relasi
        if (! Schema::hasColumn('jenis_surats', 'kategori_surat_id')) {
            Schema::table('jenis_surats', function (Blueprint $table) {
                $table->string('kategori_surat_id', 32)->nullable()->after('id');
            });
        }

        // 2) Backfill dari kolom lama
        if (Schema::hasColumn('jenis_surats', 'kategori_id')) {
            DB::statement(
                "UPDATE jenis_surats js JOIN kategori_surats ks ON js.kategori_id = CAST(SUBSTRING_INDEX(ks.kategori_surat_id, '-', -1) AS UNSIGNED) SET js.kategori_surat_id = ks.kategori_surat_id"
            );
        }

        // 3) Drop FK lama dan kolom kategori_id
        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'jenis_surats')
            ->where('CONSTRAINT_NAME', 'jenis_surats_kategori_id_foreign')
            ->exists();
        if ($fkExists) {
            Schema::table('jenis_surats', function (Blueprint $table) {
                $table->dropForeign('jenis_surats_kategori_id_foreign');
            });
        }
        if (Schema::hasColumn('jenis_surats', 'kategori_id')) {
            Schema::table('jenis_surats', function (Blueprint $table) {
                $table->dropColumn('kategori_id');
            });
        }

        // 4) (Opsional) Tambah FK baru ke kategori_surats.kategori_surat_id
        // Banyak MySQL mendukung FK ke VARCHAR PK, tapi jika gagal, kolom tetap sebagai referensi longgar
        try {
            Schema::table('jenis_surats', function (Blueprint $table) {
                $table->foreign('kategori_surat_id')->references('kategori_surat_id')->on('kategori_surats')->onDelete('restrict');
            });
        } catch (\Throwable $e) {
            // ignore FK add failure
        }

        // Biarkan pembersihan kolom sementara dilakukan di migrasi terpisah bila diperlukan
    }

    public function down(): void
    {
        if (! Schema::hasTable('jenis_surats')) {
            return;
        }
        // Remove FK baru jika ada
        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'jenis_surats')
            ->where('CONSTRAINT_NAME', 'jenis_surats_kategori_surat_id_foreign')
            ->exists();
        if ($fkExists) {
            Schema::table('jenis_surats', function (Blueprint $table) {
                $table->dropForeign('jenis_surats_kategori_surat_id_foreign');
            });
        }
        Schema::table('jenis_surats', function (Blueprint $table) {
            $table->unsignedBigInteger('kategori_id')->nullable()->after('id');
        });
        DB::statement('UPDATE jenis_surats js JOIN kategori_surats ks ON js.kategori_surat_id = ks.kategori_surat_id SET js.kategori_id = 0');
        Schema::table('jenis_surats', function (Blueprint $table) {
            $table->dropColumn('kategori_surat_id');
        });
    }
};
