<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_keuangan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal');
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->enum('sumber', ['iuran', 'donasi']);
            $table->decimal('nominal', 12, 2);
            $table->integer('referensi_id')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('metode');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_keuangan');
    }
};
