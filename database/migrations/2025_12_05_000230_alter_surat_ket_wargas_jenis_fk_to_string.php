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

        // Drop FK lama jika ada
        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'surat_ket_wargas')
            ->where('CONSTRAINT_NAME', 'surat_ket_wargas_id_jenis_surat_foreign')
            ->exists();
        if ($fkExists) {
            Schema::table('surat_ket_wargas', function (Blueprint $table) {
                $table->dropForeign('surat_ket_wargas_id_jenis_surat_foreign');
            });
        }

        // Tambah kolom baru string untuk menyimpan ID jenis-surat string
        if (! Schema::hasColumn('surat_ket_wargas', 'id_jenis_surat_new')) {
            Schema::table('surat_ket_wargas', function (Blueprint $table) {
                $table->string('id_jenis_surat_new', 32)->nullable()->after('warga_nik');
            });
        }

        // Backfill dari numeric ke string menggunakan mapping di jenis_surats
        DB::statement('UPDATE surat_ket_wargas skw JOIN jenis_surats js ON skw.id_jenis_surat = js.id SET skw.id_jenis_surat_new = js.jenis_surat_id');

        // Ganti kolom lama
        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            $table->dropColumn('id_jenis_surat');
        });
        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            $table->renameColumn('id_jenis_surat_new', 'id_jenis_surat');
        });

        // (Opsional) Tambah FK ke jenis_surats.jenis_surat_id
        try {
            Schema::table('surat_ket_wargas', function (Blueprint $table) {
                $table->foreign('id_jenis_surat')->references('jenis_surat_id')->on('jenis_surats')->onDelete('restrict');
            });
        } catch (\Throwable $e) { /* ignore */
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('surat_ket_wargas')) {
            return;
        }

        // Drop FK baru jika ada
        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'surat_ket_wargas')
            ->where('CONSTRAINT_NAME', 'surat_ket_wargas_id_jenis_surat_foreign')
            ->exists();
        if ($fkExists) {
            Schema::table('surat_ket_wargas', function (Blueprint $table) {
                $table->dropForeign('surat_ket_wargas_id_jenis_surat_foreign');
            });
        }

        // Kembalikan kolom numeric
        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_jenis_surat')->nullable()->after('warga_nik');
        });
    }
};
