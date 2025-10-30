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
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_pengajuan');
            $table->foreignId('id_warga')->constrained('wargas')->onDelete('cascade');
            $table->enum('jenis_pengaduan', [
                'infrastruktur', 
                'kebersihan', 
                'keamanan', 
                'sosial', 
                'kesehatan',
                'pendidikan',
                'ekonomi',
                'lainnya'
            ]);
            $table->text('jdl_pengaduan');
            $table->enum('status', ['pending', 'diproses', 'selesai', 'ditolak'])->default('pending');
            $table->text('detail_pengaduan');
            $table->text('bukti')->nullable(); // untuk menyimpan path file bukti
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan');
    }
};
