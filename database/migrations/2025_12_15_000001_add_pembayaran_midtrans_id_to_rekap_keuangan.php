<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rekap_keuangan')) {
            return;
        }

        Schema::table('rekap_keuangan', function (Blueprint $table) {
            if (! Schema::hasColumn('rekap_keuangan', 'PembayaranMidtrans_id')) {
                $table->string('PembayaranMidtrans_id', 50)->nullable();
            }
        });

        if (Schema::hasColumn('rekap_keuangan', 'transaksi_iuran_id')) {
            DB::statement('UPDATE rekap_keuangan SET PembayaranMidtrans_id = transaksi_iuran_id WHERE PembayaranMidtrans_id IS NULL');
        } elseif (Schema::hasColumn('rekap_keuangan', 'payment_transaction_id')) {
            DB::statement('UPDATE rekap_keuangan SET PembayaranMidtrans_id = payment_transaction_id WHERE PembayaranMidtrans_id IS NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('rekap_keuangan')) {
            return;
        }

        Schema::table('rekap_keuangan', function (Blueprint $table) {
            if (Schema::hasColumn('rekap_keuangan', 'PembayaranMidtrans_id')) {
                $table->dropColumn('PembayaranMidtrans_id');
            }
        });
    }
};
