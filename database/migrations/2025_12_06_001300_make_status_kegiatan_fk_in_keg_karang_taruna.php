<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('keg_karang_taruna')) {
            return;
        }

        Schema::table('keg_karang_taruna', function (Blueprint $table) {
            if (! Schema::hasColumn('keg_karang_taruna', 'status_kegiatan_new')) {
                $table->string('status_kegiatan_new', 32)->nullable()->after('penanggung_jawab');
            }
        });

        if (Schema::hasColumn('keg_karang_taruna', 'status_kegiatan')) {
            DB::statement('UPDATE keg_karang_taruna k JOIN statuses s ON LOWER(k.status_kegiatan) = LOWER(s.keterangan) SET k.status_kegiatan_new = s.status_id WHERE k.status_kegiatan IS NOT NULL');
        } else {
            $default = DB::table('statuses')
                ->whereRaw('LOWER(keterangan) = ?', ['terjadwal'])
                ->where('fitur', 'kegiatan')
                ->value('status_id');
            if ($default) {
                DB::table('keg_karang_taruna')->update(['status_kegiatan_new' => $default]);
            }
        }

        Schema::table('keg_karang_taruna', function (Blueprint $table) {
            if (Schema::hasColumn('keg_karang_taruna', 'status_kegiatan')) {
                $table->dropColumn('status_kegiatan');
            }
        });

        Schema::table('keg_karang_taruna', function (Blueprint $table) {
            $table->renameColumn('status_kegiatan_new', 'status_kegiatan');
        });

        Schema::table('keg_karang_taruna', function (Blueprint $table) {
            try {
                $table->foreign('status_kegiatan')->references('status_id')->on('statuses')->onDelete('restrict');
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('keg_karang_taruna')) {
            return;
        }
        Schema::table('keg_karang_taruna', function (Blueprint $table) {
            try {
                $table->dropForeign(['status_kegiatan']);
            } catch (\Throwable $e) {
            }
        });
    }
};
