<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transaksi_iurans')) {
            Schema::drop('transaksi_iurans');
        }
    }

    public function down(): void
    {
        // No-op: we don't want to recreate the wrong table name
    }
};
