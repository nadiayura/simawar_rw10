<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pembayaran_tunai')) {
            Schema::drop('pembayaran_tunai');
        }
        Schema::create('pembayaran_tunai', function (Blueprint $table) {
            $table->string('PembayaranTunai_id', 40)->primary();
            $table->string('tagihan_iuran_id', 40);
            $table->decimal('nominal_dibayarkan', 15, 2);
            $table->string('status_id', 64);
            $table->timestamps();
        });
        Schema::table('pembayaran_tunai', function (Blueprint $table) {
            $table->foreign('tagihan_iuran_id')->references('tagihan_iuran_id')->on('tagihan_iuran_wargas')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('status_id')->references('status_id')->on('statuses')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_tunai');
    }
};
