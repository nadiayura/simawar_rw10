<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ketua_rts')) {
            return;
        }

        Schema::table('ketua_rts', function (Blueprint $table) {
            if (! Schema::hasColumn('ketua_rts', 'alamat')) {
                $table->string('alamat')->nullable()->after('warga_nik');
            }
        });

        DB::statement('UPDATE ketua_rts k JOIN wargas w ON k.warga_nik = w.warga_nik SET k.alamat = w.alamat WHERE k.warga_nik IS NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('ketua_rts')) {
            return;
        }

        Schema::table('ketua_rts', function (Blueprint $table) {
            if (Schema::hasColumn('ketua_rts', 'alamat')) {
                $table->dropColumn('alamat');
            }
        });
    }
};
