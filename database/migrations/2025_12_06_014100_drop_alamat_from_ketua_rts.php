<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ketua_rts') && Schema::hasColumn('ketua_rts', 'alamat')) {
            Schema::table('ketua_rts', function (Blueprint $table) {
                $table->dropColumn('alamat');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ketua_rts') && ! Schema::hasColumn('ketua_rts', 'alamat')) {
            Schema::table('ketua_rts', function (Blueprint $table) {
                $table->string('alamat')->nullable();
            });
        }
    }
};
