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
        Schema::create('wargas', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('agama');
            $table->enum('status_tinggal', ['Tetap', 'Kontrak', 'Sementara']);
            $table->text('alamat');
            $table->string('id_rt', 3);
            $table->string('rw', 3);
            $table->string('no_hp', 15)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();

            // Index untuk pencarian yang sering dilakukan
            $table->index('nik');
            $table->index('id_rt');
            $table->index('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wargas');
    }
};
