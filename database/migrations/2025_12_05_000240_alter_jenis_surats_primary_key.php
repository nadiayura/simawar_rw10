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

        // Pastikan semua FK yang mereferensikan jenis_surats.id di-drop terlebih dahulu
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('REFERENCED_TABLE_NAME', 'jenis_surats')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->get(['TABLE_NAME', 'CONSTRAINT_NAME']);

        foreach ($refs as $ref) {
            Schema::table($ref->TABLE_NAME, function (Blueprint $table) use ($ref) {
                $table->dropForeign($ref->CONSTRAINT_NAME);
            });
        }

        // Hapus AUTO_INCREMENT pada kolom id sebelum drop PK untuk menghindari error 1075
        DB::statement('ALTER TABLE jenis_surats MODIFY id BIGINT UNSIGNED NOT NULL');

        // Jadikan jenis_surat_id NOT NULL lalu set sebagai PRIMARY KEY
        DB::statement('ALTER TABLE jenis_surats MODIFY jenis_surat_id VARCHAR(32) NOT NULL');
        DB::statement('ALTER TABLE jenis_surats DROP PRIMARY KEY, ADD PRIMARY KEY (jenis_surat_id)');

        // Hapus kolom id lama
        Schema::table('jenis_surats', function (Blueprint $table) {
            if (Schema::hasColumn('jenis_surats', 'id')) {
                $table->dropColumn('id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('jenis_surats')) {
            return;
        }

        // Drop PK pada jenis_surat_id
        Schema::table('jenis_surats', function (Blueprint $table) {
            $table->dropPrimary();
        });

        // Tambahkan kembali kolom id sebagai auto-increment primary key
        Schema::table('jenis_surats', function (Blueprint $table) {
            if (! Schema::hasColumn('jenis_surats', 'id')) {
                $table->bigIncrements('id')->first();
            }
        });

        // Biarkan kolom jenis_surat_id tetap ada untuk keamanan data
    }
};
