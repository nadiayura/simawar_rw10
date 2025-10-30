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
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom yang redundan (index akan terhapus otomatis)
            $table->dropColumn(['rt_area', 'rw_area']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan kolom jika rollback
            $table->string('rt_area')->nullable();
            $table->string('rw_area')->nullable();
            
            // Kembalikan index
            $table->index(['role_id', 'rt_area']);
            $table->index(['role_id', 'rw_area']);
        });
    }
};
