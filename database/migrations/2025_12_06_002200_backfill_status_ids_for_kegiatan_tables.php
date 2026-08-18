<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('keg_kesehatans')) {
            $defaultKesehatan = DB::table('statuses')
                ->where('fitur', 'keg_kesehatan')
                ->whereRaw('LOWER(keterangan) = ?', ['selesai'])
                ->value('status_id');
            if ($defaultKesehatan) {
                DB::table('keg_kesehatans')
                    ->whereNull('status_kegiatan')
                    ->update(['status_kegiatan' => $defaultKesehatan]);
            }
        }

        if (Schema::hasTable('keg_karang_taruna')) {
            $defaultKarangTaruna = DB::table('statuses')
                ->where('fitur', 'keg_karang_taruna')
                ->whereRaw('LOWER(keterangan) = ?', ['terjadwal'])
                ->value('status_id');
            if ($defaultKarangTaruna) {
                DB::table('keg_karang_taruna')
                    ->whereNull('status_kegiatan')
                    ->update(['status_kegiatan' => $defaultKarangTaruna]);
            }
        }
    }

    public function down(): void
    {
        // No-op: data backfill cannot be safely reverted.
    }
};
