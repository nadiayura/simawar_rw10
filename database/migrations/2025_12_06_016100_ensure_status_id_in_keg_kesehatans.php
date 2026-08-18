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
            if (! Schema::hasColumn('keg_kesehatans', 'status_id')) {
                $table->string('status_id', 32)->nullable()->after('dokumentasi');
            }
        });

        // Backfill if empty using fitur keg_warga
        $default = DB::table('statuses')
            ->where('fitur', 'keg_warga')
            ->whereRaw('LOWER(keterangan) = ?', ['terjadwal'])
            ->value('status_id');
        if ($default) {
            DB::table('keg_kesehatans')
                ->whereNull('status_id')
                ->update(['status_id' => $default]);
        }

        // Ensure FK exists
        try {
            Schema::table('keg_kesehatans', function (Blueprint $table) {
                $table->foreign('status_id')->references('status_id')->on('statuses')->onDelete('restrict');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('keg_kesehatans')) {
            return;
        }
        try {
            Schema::table('keg_kesehatans', function (Blueprint $table) {
                $table->dropForeign(['status_id']);
            });
        } catch (\Throwable $e) {
        }
        Schema::table('keg_kesehatans', function (Blueprint $table) {
            if (Schema::hasColumn('keg_kesehatans', 'status_id')) {
                $table->dropColumn('status_id');
            }
        });
    }
};
