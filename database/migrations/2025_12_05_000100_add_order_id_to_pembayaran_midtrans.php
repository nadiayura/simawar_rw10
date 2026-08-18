<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pembayaran_midtrans')) {
            return;
        }
        if (! Schema::hasColumn('pembayaran_midtrans', 'order_id')) {
            Schema::table('pembayaran_midtrans', function (Blueprint $table) {
                $table->string('order_id', 100)->nullable()->after('redirect_url');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pembayaran_midtrans')) {
            return;
        }
        if (Schema::hasColumn('pembayaran_midtrans', 'order_id')) {
            Schema::table('pembayaran_midtrans', function (Blueprint $table) {
                $table->dropColumn('order_id');
            });
        }
    }
};
