<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jenis_surats') && Schema::hasColumn('jenis_surats', 'kategori_surat_id')) {
            $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'jenis_surats')
                ->where('COLUMN_NAME', 'kategori_surat_id')
                ->whereNotNull('CONSTRAINT_NAME')
                ->where('CONSTRAINT_NAME', '!=', 'PRIMARY')
                ->exists();

            if ($fkExists) {
                Schema::table('jenis_surats', function (Blueprint $table) {
                    $table->dropForeign('jenis_surats_kategori_surat_id_foreign');
                });
            }

            Schema::table('jenis_surats', function (Blueprint $table) {
                $table->dropColumn('kategori_surat_id');
            });
        }

        if (Schema::hasTable('kategori_surats')) {
            Schema::drop('kategori_surats');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kategori_surats')) {
            return;
        }

        Schema::create('kategori_surats', function (Blueprint $table) {
            $table->string('kategori_surat_id', 32)->primary();
            $table->string('nama', 64)->unique();
        });

        if (Schema::hasTable('jenis_surats') && ! Schema::hasColumn('jenis_surats', 'kategori_surat_id')) {
            Schema::table('jenis_surats', function (Blueprint $table) {
                $table->string('kategori_surat_id', 32)->nullable()->after('jenis_surat_id');
            });
        }
    }
};
