<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pembayaran_midtrans')) {
            return;
        }

        $constraints = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'pembayaran_midtrans')
            ->where('COLUMN_NAME', 'tagihan_iuran_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->get(['CONSTRAINT_NAME']);

        if ($constraints->count() > 0) {
            Schema::table('pembayaran_midtrans', function (Blueprint $table) use ($constraints) {
                foreach ($constraints as $c) {
                    $table->dropForeign($c->CONSTRAINT_NAME);
                }
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pembayaran_midtrans') || ! Schema::hasTable('tagihan_iuran_wargas')) {
            return;
        }

        // Try to restore a conventional FK if needed
        $hasFk = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'pembayaran_midtrans')
            ->where('COLUMN_NAME', 'tagihan_iuran_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if (! $hasFk) {
            Schema::table('pembayaran_midtrans', function (Blueprint $table) {
                $table->foreign('tagihan_iuran_id')
                    ->references('tagihan_iuran_id')
                    ->on('tagihan_iuran_wargas');
            });
        }
    }
};
