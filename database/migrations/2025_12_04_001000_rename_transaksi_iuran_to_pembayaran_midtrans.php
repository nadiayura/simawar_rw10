<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transaksi_iuran') && ! Schema::hasTable('pembayaran_midtrans')) {
            Schema::rename('transaksi_iuran', 'pembayaran_midtrans');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pembayaran_midtrans') && ! Schema::hasTable('transaksi_iuran')) {
            Schema::rename('pembayaran_midtrans', 'transaksi_iuran');
        }
    }
};
