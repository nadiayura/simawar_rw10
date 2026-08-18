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
        Schema::table('wargas', function (Blueprint $table) {
            $table->foreign('iuran_id')->references('iuran_id')->on('iurans')->onDelete('set null');
            $table->foreign('no_rt_id')->references('no_rt_id')->on('no_rts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wargas', function (Blueprint $table) {
            $table->dropForeign(['iuran_id']);
            $table->dropForeign(['no_rt_id']);
        });
    }
};
