<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('iurans')) {
            return;
        }

        // Drop semua FK yang mereferensikan iurans.id
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('REFERENCED_TABLE_NAME', 'iurans')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->get(['TABLE_NAME', 'CONSTRAINT_NAME']);
        foreach ($refs as $ref) {
            Schema::table($ref->TABLE_NAME, function (Blueprint $table) use ($ref) {
                $table->dropForeign($ref->CONSTRAINT_NAME);
            });
        }

        // Pastikan iuran_id NOT NULL, lalu jadikan PRIMARY KEY
        DB::statement('ALTER TABLE iurans MODIFY iuran_id VARCHAR(32) NOT NULL');

        // Hapus AUTO_INCREMENT pada kolom id sebelum drop PK untuk menghindari error 1075
        DB::statement('ALTER TABLE iurans MODIFY id BIGINT UNSIGNED NOT NULL');

        DB::statement('ALTER TABLE iurans DROP PRIMARY KEY, ADD PRIMARY KEY (iuran_id)');

        // Hapus kolom id lama
        Schema::table('iurans', function (Blueprint $table) {
            if (Schema::hasColumn('iurans', 'id')) {
                $table->dropColumn('id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('iurans')) {
            return;
        }
        Schema::table('iurans', function (Blueprint $table) {
            $table->dropPrimary();
            $table->bigIncrements('id')->first();
        });
        // Biarkan iuran_id tetap ada
    }
};
