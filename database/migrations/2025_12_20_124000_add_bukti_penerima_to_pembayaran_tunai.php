<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pembayaran_tunai')) {
            return;
        }
        Schema::table('pembayaran_tunai', function (Blueprint $table) {
            if (! Schema::hasColumn('pembayaran_tunai', 'bukti')) {
                $table->json('bukti')->nullable()->after('status_id');
            }
            if (! Schema::hasColumn('pembayaran_tunai', 'penerima')) {
                $table->string('penerima', 128)->nullable()->after('bukti');
            }
            if (! Schema::hasColumn('pembayaran_tunai', 'bulan_bayar')) {
                $table->unsignedTinyInteger('bulan_bayar')->nullable()->after('penerima');
            }
            if (! Schema::hasColumn('pembayaran_tunai', 'periode_iuran_id')) {
                $table->string('periode_iuran_id', 32)->nullable()->after('bulan_bayar');
            }
        });
        if (Schema::hasTable('periode_iurans') && Schema::hasColumn('pembayaran_tunai', 'periode_iuran_id')) {
            Schema::table('pembayaran_tunai', function (Blueprint $table) {
                $table->foreign('periode_iuran_id')
                    ->references('periode_iuran_id')
                    ->on('periode_iurans')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pembayaran_tunai')) {
            return;
        }
        Schema::table('pembayaran_tunai', function (Blueprint $table) {
            if (Schema::hasColumn('pembayaran_tunai', 'penerima')) {
                $table->dropColumn('penerima');
            }
            if (Schema::hasColumn('pembayaran_tunai', 'bukti')) {
                $table->dropColumn('bukti');
            }
            if (Schema::hasColumn('pembayaran_tunai', 'bulan_bayar')) {
                $table->dropColumn('bulan_bayar');
            }
            if (Schema::hasColumn('pembayaran_tunai', 'periode_iuran_id')) {
                // Drop FK automatically with column on most DBs; explicit drop may require constraint name
                $table->dropColumn('periode_iuran_id');
            }
        });
    }
};
