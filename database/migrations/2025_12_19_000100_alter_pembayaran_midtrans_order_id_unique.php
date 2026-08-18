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

        // Ensure column exists and widen length for safety
        if (Schema::hasColumn('pembayaran_midtrans', 'order_id')) {
            DB::statement('ALTER TABLE pembayaran_midtrans MODIFY order_id VARCHAR(150) NULL');
        } else {
            Schema::table('pembayaran_midtrans', function (Blueprint $table) {
                $table->string('order_id', 150)->nullable()->after('redirect_url');
            });
        }

        // Add unique index for order_id if not exists
        $idxExists = DB::table('information_schema.statistics')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'pembayaran_midtrans')
            ->where('INDEX_NAME', 'pembayaran_midtrans_order_id_unique')
            ->exists();

        if (! $idxExists) {
            Schema::table('pembayaran_midtrans', function (Blueprint $table) {
                $table->unique('order_id', 'pembayaran_midtrans_order_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pembayaran_midtrans')) {
            return;
        }

        // Drop unique index if exists
        $idxExists = DB::table('information_schema.statistics')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'pembayaran_midtrans')
            ->where('INDEX_NAME', 'pembayaran_midtrans_order_id_unique')
            ->exists();

        if ($idxExists) {
            Schema::table('pembayaran_midtrans', function (Blueprint $table) {
                $table->dropUnique('pembayaran_midtrans_order_id_unique');
            });
        }
    }
};
