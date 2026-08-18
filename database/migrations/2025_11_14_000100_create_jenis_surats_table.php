<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_surats')) {
            Schema::create('jenis_surats', function (Blueprint $table) {
                $table->id();
                $table->enum('kategori', ['Umum', 'Kependudukan', 'Pernikahan', 'Pertanahan']);
                $table->string('nama_surat', 255);
                $table->text('deskripsi')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_surats');
    }
};
