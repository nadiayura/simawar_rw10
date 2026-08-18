<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('keg_karang_taruna')) {
            return;
        }

        // MySQL: modify DATE to DATETIME NULL
        try {
            DB::statement('ALTER TABLE keg_karang_taruna MODIFY `tanggal` DATETIME NULL');
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('keg_karang_taruna')) {
            return;
        }
        try {
            DB::statement('ALTER TABLE keg_karang_taruna MODIFY `tanggal` DATE NULL');
        } catch (\Throwable $e) {
        }
    }
};
