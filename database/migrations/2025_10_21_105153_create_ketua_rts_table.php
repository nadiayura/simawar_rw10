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
        Schema::create('ketua_rts', function (Blueprint $table) {
            $table->id();
            $table->string('no_rt', 3);
            $table->foreignId('id_warga')->constrained('wargas')->onDelete('cascade');
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index untuk pencarian yang sering dilakukan
            $table->index('no_rt');
            $table->index('id_warga');
            $table->index('is_active');

            // Unique constraint untuk memastikan hanya ada satu ketua RT aktif per RT
            $table->unique(['no_rt', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ketua_rts');
    }
};
