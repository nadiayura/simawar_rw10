<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas') || ! Schema::hasTable('iurans')) {
            return;
        }

        $hasFk = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'tagihan_iuran_wargas')
            ->where('COLUMN_NAME', 'iuran_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
        if ($hasFk) {
            return;
        }

        try {
            // Pastikan tipe kolom sesuai
            DB::statement('ALTER TABLE tagihan_iuran_wargas MODIFY iuran_id VARCHAR(32) NULL');
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
                $table->foreign('iuran_id')->references('iuran_id')->on('iurans')->onDelete('restrict');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas')) {
            return;
        }

        try {
            $fk = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'tagihan_iuran_wargas')
                ->where('COLUMN_NAME', 'iuran_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->value('CONSTRAINT_NAME');
            if ($fk) {
                DB::statement('ALTER TABLE `tagihan_iuran_wargas` DROP FOREIGN KEY `'.$fk.'`');
            }
        } catch (\Throwable $e) {
        }
    }
};
