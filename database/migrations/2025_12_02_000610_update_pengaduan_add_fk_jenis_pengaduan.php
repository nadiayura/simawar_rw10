<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaduan', function (Blueprint $table) {
            if (! Schema::hasColumn('pengaduan', 'jenis_pengaduan_id')) {
                $table->unsignedBigInteger('jenis_pengaduan_id')->nullable()->after('warga_nik');
            }
        });

        if (Schema::hasColumn('pengaduan', 'jenis_pengaduan')) {
            DB::statement('UPDATE pengaduan p JOIN jenis_pengaduans jp ON LOWER(p.jenis_pengaduan) = LOWER(jp.nama) SET p.jenis_pengaduan_id = jp.id');
        }

        Schema::table('pengaduan', function (Blueprint $table) {
            $table->foreign('jenis_pengaduan_id')->references('id')->on('jenis_pengaduans')->onDelete('restrict');
            if (Schema::hasColumn('pengaduan', 'jenis_pengaduan')) {
                $table->dropColumn('jenis_pengaduan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengaduan', function (Blueprint $table) {
            $table->string('jenis_pengaduan', 50)->nullable();
            $table->dropForeign(['jenis_pengaduan_id']);
            $table->dropColumn('jenis_pengaduan_id');
        });
    }
};
