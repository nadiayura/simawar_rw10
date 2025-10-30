<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, clear any existing data that might cause JSON conversion issues
        DB::table('keg_kesehatans')->update([
            'rincian_peserta' => '{"anak":0,"bayi":0,"ibu_hamil":0,"remaja":0}',
            'aktivitas_dilakukan' => '[]'
        ]);

        Schema::table('keg_kesehatans', function (Blueprint $table) {
            $table->json('rincian_peserta')->change();
            $table->json('aktivitas_dilakukan')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keg_kesehatans', function (Blueprint $table) {
            $table->text('rincian_peserta')->change();
            $table->text('aktivitas_dilakukan')->change();
        });
    }
};
