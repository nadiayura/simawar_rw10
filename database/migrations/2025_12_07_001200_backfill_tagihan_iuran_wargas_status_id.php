<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas') || ! Schema::hasTable('statuses')) {
            return;
        }

        $default = DB::table('statuses')
            ->where('fitur', 'keuangan')
            ->whereRaw('LOWER(keterangan) = ?', ['belum bayar'])
            ->value('status_id');

        if ($default) {
            DB::table('tagihan_iuran_wargas')
                ->whereNull('status_id')
                ->update(['status_id' => $default]);
        }
    }

    public function down(): void
    {
        // No-op: data backfill tidak bisa di-rollback dengan aman.
    }
};
