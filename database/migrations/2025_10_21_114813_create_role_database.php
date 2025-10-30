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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // warga, rt, rw
            $table->string('display_name'); // Warga, Ketua RT, Ketua RW
            $table->text('description')->nullable();
            $table->string('level'); // warga, rt, rw
            $table->integer('hierarchy_level'); // 1=warga, 2=rt, 3=rw
            $table->timestamps();
            
            $table->index(['name', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
