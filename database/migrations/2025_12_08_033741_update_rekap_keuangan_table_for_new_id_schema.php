<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rekap_keuangan', function (Blueprint $table) {
            if (! Schema::hasColumn('rekap_keuangan', 'rekap_keuangan_id')) {
                $table->string('rekap_keuangan_id')->after('id')->nullable();
            }
        });

        // Isi rekap_keuangan_id dengan nilai sementara dari id
        DB::table('rekap_keuangan')->orderBy('tanggal')->get()->each(function ($item, $key) {
            DB::table('rekap_keuangan')
                ->where('id', $item->id)
                ->update(['rekap_keuangan_id' => 'TEMP-'.($key + 1)]);
        });

        Schema::table('rekap_keuangan', function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
            $table->renameColumn('rekap_keuangan_id', 'id');
        });

        Schema::table('rekap_keuangan', function (Blueprint $table) {
            $table->renameColumn('id', 'rekap_keuangan_id');
            $table->primary('rekap_keuangan_id');
            $table->renameColumn('jenis', 'jenis_trans');
            $table->renameColumn('payment_transaction_id', 'transaksi_iuran_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_keuangan', function (Blueprint $table) {
            $table->renameColumn('jenis_trans', 'jenis');
            $table->renameColumn('transaksi_iuran_id', 'payment_transaction_id');
            $table->dropPrimary();
            $table->renameColumn('rekap_keuangan_id', 'id');
            $table->primary('id');
        });
    }
};
