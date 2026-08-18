<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('periode_iurans')) {
            return;
        }

        // Drop semua FK yang mereferensikan periode_iurans.id
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('REFERENCED_TABLE_NAME', 'periode_iurans')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->get(['TABLE_NAME', 'CONSTRAINT_NAME']);
        foreach ($refs as $ref) {
            Schema::table($ref->TABLE_NAME, function (Blueprint $table) use ($ref) {
                $table->dropForeign($ref->CONSTRAINT_NAME);
            });
        }

        // Pastikan periode_iuran_id NOT NULL, set sebagai PK
        DB::statement('ALTER TABLE periode_iurans MODIFY periode_iuran_id VARCHAR(32) NOT NULL');

        // Hapus AUTO_INCREMENT dari id lalu ganti PK
        DB::statement('ALTER TABLE periode_iurans MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE periode_iurans DROP PRIMARY KEY, ADD PRIMARY KEY (periode_iuran_id)');

        // Hapus kolom id lama
        Schema::table('periode_iurans', function (Blueprint $table) {
            if (Schema::hasColumn('periode_iurans', 'id')) {
                $table->dropColumn('id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('periode_iurans')) {
            return;
        }
        Schema::table('periode_iurans', function (Blueprint $table) {
            $table->dropPrimary();
            $table->bigIncrements('id')->first();
        });
        // Biarkan periode_iuran_id tetap ada
    }
};
