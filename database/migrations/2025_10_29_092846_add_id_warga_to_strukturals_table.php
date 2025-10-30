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
        Schema::table('strukturals', function (Blueprint $table) {
            $table->unsignedBigInteger('id_warga')->nullable()->after('nama');
            $table->foreign('id_warga')->references('id')->on('wargas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('strukturals', function (Blueprint $table) {
            $table->dropForeign(['id_warga']);
            $table->dropColumn('id_warga');
        });
    }
};
