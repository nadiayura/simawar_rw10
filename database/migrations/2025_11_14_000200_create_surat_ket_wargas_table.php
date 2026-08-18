<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('surat_ket_wargas')) {
            Schema::create('surat_ket_wargas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_warga')->constrained('wargas');
                $table->foreignId('id_jenis_surat')->constrained('jenis_surats');
                $table->date('tgl_pengajuan');
                $table->text('keperluan');
                $table->json('dok_pendukung')->nullable();
                $table->enum('status', ['Diajukan', 'Diproses', 'Disetujui', 'Ditolak', 'Selesai'])->default('Diajukan');
                $table->text('catatan_admin')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_ket_wargas');
    }
};
