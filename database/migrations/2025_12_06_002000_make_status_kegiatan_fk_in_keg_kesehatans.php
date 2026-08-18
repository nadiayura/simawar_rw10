<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('keg_kesehatans')) {
            return;
        }

        Schema::table('keg_kesehatans', function (Blueprint $table) {
            if (! Schema::hasColumn('keg_kesehatans', 'status_kegiatan_new')) {
                $table->string('status_kegiatan_new', 32)->nullable()->after('dokumentasi');
            }
        });

        if (Schema::hasColumn('keg_kesehatans', 'status_kegiatan')) {
            DB::statement("UPDATE keg_kesehatans k JOIN statuses s ON LOWER(k.status_kegiatan) = LOWER(s.keterangan) AND s.fitur = 'kegiatan' SET k.status_kegiatan_new = s.status_id WHERE k.status_kegiatan IS NOT NULL");
        } else {
            $default = DB::table('statuses')
                ->where('fitur', 'kegiatan')
                ->whereRaw('LOWER(keterangan) = ?', ['selesai'])
                ->value('status_id');
            if ($default) {
                DB::table('keg_kesehatans')->update(['status_kegiatan_new' => $default]);
            }
        }

        Schema::table('keg_kesehatans', function (Blueprint $table) {
            if (Schema::hasColumn('keg_kesehatans', 'status_kegiatan')) {
                $table->dropColumn('status_kegiatan');
            }
        });

        Schema::table('keg_kesehatans', function (Blueprint $table) {
            $table->renameColumn('status_kegiatan_new', 'status_kegiatan');
        });

        Schema::table('keg_kesehatans', function (Blueprint $table) {
            try {
                $table->foreign('status_kegiatan')->references('status_id')->on('statuses')->onDelete('restrict');
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('keg_kesehatans')) {
            return;
        }
        Schema::table('keg_kesehatans', function (Blueprint $table) {
            try {
                $table->dropForeign(['status_kegiatan']);
            } catch (\Throwable $e) {
            }
        });
    }
};
