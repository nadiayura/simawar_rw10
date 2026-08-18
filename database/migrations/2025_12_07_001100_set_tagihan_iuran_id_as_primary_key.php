<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas')) {
            return;
        }

        // Pastikan kolom NOT NULL agar bisa dijadikan PRIMARY KEY
        DB::statement('ALTER TABLE tagihan_iuran_wargas MODIFY tagihan_iuran_id VARCHAR(150) NOT NULL');

        // Drop PK jika ada, lalu set PK ke tagihan_iuran_id
        try {
            DB::statement('ALTER TABLE tagihan_iuran_wargas DROP PRIMARY KEY');
        } catch (\Throwable $e) {
        }

        DB::statement('ALTER TABLE tagihan_iuran_wargas ADD PRIMARY KEY (`tagihan_iuran_id`)');
    }

    public function down(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas')) {
            return;
        }
        try {
            DB::statement('ALTER TABLE tagihan_iuran_wargas DROP PRIMARY KEY');
        } catch (\Throwable $e) {
        }
        // Biarkan kolom tetap NOT NULL
    }
};
