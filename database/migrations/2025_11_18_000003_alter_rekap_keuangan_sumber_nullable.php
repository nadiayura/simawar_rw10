<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE rekap_keuangan MODIFY sumber ENUM('iuran','donasi') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE rekap_keuangan MODIFY sumber ENUM('iuran','donasi') NOT NULL");
    }
};
