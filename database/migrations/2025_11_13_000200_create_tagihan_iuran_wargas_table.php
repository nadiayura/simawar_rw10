<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas')) {
            Schema::create('tagihan_iuran_wargas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warga_id')->constrained('wargas');
                $table->foreignId('iuran_id')->constrained('iurans');
                $table->foreignId('periode_id')->constrained('periode_iurans');
                $table->decimal('nominal_tagihan', 15, 2);
                $table->enum('status_tagihan', ['belum_bayar', 'menunggu_pembayaran', 'lunas', 'kedaluwarsa'])->default('belum_bayar');
                $table->date('tanggal_lunas')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_iuran_wargas');
    }
};
