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
        Schema::create('strukturals', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jabatan'); // Ketua RW, Ketua RT 01, Ketua RT 02, dst
            $table->string('no_rt')->nullable(); // null untuk Ketua RW, diisi untuk Ketua RT
            $table->string('periode_mulai');
            $table->string('periode_selesai');
            $table->string('foto')->nullable(); // path ke foto
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('urutan')->default(0); // untuk sorting
            $table->timestamps();
            
            // Index untuk performance
            $table->index(['is_active', 'urutan']);
            $table->index('no_rt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strukturals');
    }
};
