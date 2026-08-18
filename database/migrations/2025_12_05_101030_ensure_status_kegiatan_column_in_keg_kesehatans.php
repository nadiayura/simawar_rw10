<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('keg_kesehatans')) {
            return;
        }
        Schema::table('keg_kesehatans', function (Blueprint $table) {
            if (! Schema::hasColumn('keg_kesehatans', 'status_kegiatan')) {
                $table->string('status_kegiatan', 32)->default('Selesai')->after('dokumentasi');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('keg_kesehatans')) {
            return;
        }
        Schema::table('keg_kesehatans', function (Blueprint $table) {
            if (Schema::hasColumn('keg_kesehatans', 'status_kegiatan')) {
                $table->dropColumn('status_kegiatan');
            }
        });
    }
};
