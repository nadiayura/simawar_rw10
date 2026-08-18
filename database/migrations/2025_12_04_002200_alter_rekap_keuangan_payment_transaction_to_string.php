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

        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'rekap_keuangan')
            ->where('CONSTRAINT_NAME', 'rekap_keuangan_payment_transaction_id_foreign')
            ->exists();
        if ($fkExists) {
            Schema::table('rekap_keuangan', function (Blueprint $table) {
                $table->dropForeign('rekap_keuangan_payment_transaction_id_foreign');
            });
        }

        if (Schema::hasColumn('rekap_keuangan', 'payment_transaction_id')) {
            DB::statement('ALTER TABLE rekap_keuangan MODIFY payment_transaction_id VARCHAR(30) NULL');
        } else {
            Schema::table('rekap_keuangan', function (Blueprint $table) {
                $table->string('payment_transaction_id', 30)->nullable()->after('nominal');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('rekap_keuangan')) {
            return;
        }

        DB::statement('ALTER TABLE rekap_keuangan MODIFY payment_transaction_id BIGINT UNSIGNED NULL');
    }
};
