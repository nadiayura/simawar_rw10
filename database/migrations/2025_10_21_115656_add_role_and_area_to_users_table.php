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
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            $table->string('rt_area')->nullable(); // RT yang dikelola (untuk user dengan role RT)
            $table->string('rw_area')->nullable(); // RW yang dikelola (untuk user dengan role RW)
            $table->foreignId('warga_id')->nullable()->constrained('wargas')->onDelete('set null'); // Relasi ke warga jika user adalah warga
            
            $table->index(['role_id', 'rt_area']);
            $table->index(['role_id', 'rw_area']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['warga_id']);
            $table->dropIndex(['role_id', 'rt_area']);
            $table->dropIndex(['role_id', 'rw_area']);
            $table->dropColumn(['role_id', 'rt_area', 'rw_area', 'warga_id']);
        });
    }
};
