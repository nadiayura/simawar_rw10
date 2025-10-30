<?php

use Illuminate\Container\Attributes\DB;
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
        Schema::table('keg_kesehatans', function (Blueprint $table) {
            // Clear existing data first to avoid conversion issues
            DB::table('keg_kesehatans')->update(['dokumentasi' => null]);
            
            // Change dokumentasi column to JSON
            $table->json('dokumentasi')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keg_kesehatans', function (Blueprint $table) {
            // Change dokumentasi column back to string
            $table->string('dokumentasi')->nullable()->change();
        });
    }
};
