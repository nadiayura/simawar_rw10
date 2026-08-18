<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Tambah kolom tanpa FK dulu
        Schema::table('rekap_keuangan', function (Blueprint $table) {
            if (! Schema::hasColumn('rekap_keuangan', 'payment_transaction_id')) {
                $table->unsignedBigInteger('payment_transaction_id')->nullable()->after('nominal');
            }
        });

        // 2) Backfill hanya yang memang ada di payment_transactions
        if (Schema::hasColumn('rekap_keuangan', 'referensi_id')) {
            DB::statement('UPDATE rekap_keuangan rk JOIN payment_transactions pt ON rk.referensi_id = pt.id SET rk.payment_transaction_id = pt.id WHERE rk.payment_transaction_id IS NULL');
        }

        // 3) Tambah FK setelah data valid (hindari duplikasi nama constraint)
        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'rekap_keuangan')
            ->where('CONSTRAINT_NAME', 'rekap_keuangan_payment_transaction_id_foreign')
            ->exists();

        if (! $fkExists) {
            Schema::table('rekap_keuangan', function (Blueprint $table) {
                $table->foreign('payment_transaction_id')
                    ->references('id')
                    ->on('payment_transactions')
                    ->nullOnDelete();
            });
        }

        // 4) Hapus referensi_id lama
        if (Schema::hasColumn('rekap_keuangan', 'referensi_id')) {
            Schema::table('rekap_keuangan', function (Blueprint $table) {
                $table->dropColumn('referensi_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('rekap_keuangan', function (Blueprint $table) {
            if (! Schema::hasColumn('rekap_keuangan', 'referensi_id')) {
                $table->unsignedBigInteger('referensi_id')->nullable();
            }
        });

        DB::statement('UPDATE rekap_keuangan SET referensi_id = payment_transaction_id WHERE referensi_id IS NULL');

        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'rekap_keuangan')
            ->where('CONSTRAINT_NAME', 'rekap_keuangan_payment_transaction_id_foreign')
            ->exists();

        Schema::table('rekap_keuangan', function (Blueprint $table) use ($fkExists) {
            if ($fkExists) {
                $table->dropForeign('rekap_keuangan_payment_transaction_id_foreign');
            }
            if (Schema::hasColumn('rekap_keuangan', 'payment_transaction_id')) {
                $table->dropColumn('payment_transaction_id');
            }
        });
    }
};
