<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('no_rts', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 3)->unique(); // 01, 02, dst
            $table->string('rw', 3)->default('010');  // opsional: RW terkait
            // $table->string('nama')->nullable();   // opsional: nama/deskripsi RT
            $table->timestamps();

            $table->index('rw');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('no_rts');
    }
};
