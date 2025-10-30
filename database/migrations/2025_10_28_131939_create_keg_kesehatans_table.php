<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('keg_kesehatans', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_kegiatan'); // Jenis kegiatan (Posyandu, Posmaja, dll)
            $table->string('nama_kegiatan'); // Nama kegiatan
            $table->date('tgl'); // Tanggal kegiatan
            $table->string('penanggung_jawab'); // Penanggung jawab kegiatan
            $table->integer('jumlah_peserta')->default(0); // Jumlah peserta
            $table->text('rincian_peserta')->nullable(); // Rincian peserta
            $table->text('aktivitas_dilakukan')->nullable(); // Ringkasan aktivitas
            $table->text('hasil_pelaksanaan')->nullable(); // Ringkasan hasil kegiatan
            $table->string('dokumentasi')->nullable(); // Path file dokumentasi
            $table->enum('status_kegiatan', ['Terjadwal', 'Selesai', 'Dibatalkan'])->default('Selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keg_kesehatans');
    }
};
