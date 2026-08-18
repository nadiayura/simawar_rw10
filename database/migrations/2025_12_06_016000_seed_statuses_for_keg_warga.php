<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }

        $now = now();
        $names = ['Terjadwal', 'Selesai', 'Dibatalkan'];
        foreach ($names as $name) {
            $exists = DB::table('statuses')
                ->where('fitur', 'keg_warga')
                ->whereRaw('LOWER(keterangan) = ?', [strtolower($name)])
                ->exists();
            if (! $exists) {
                $id = 'KGW-'.strtoupper(substr($name, 0, 3)).'-'.substr(md5('keg_warga'.$name.$now), 0, 6);
                DB::table('statuses')->insert([
                    'status_id' => $id,
                    'keterangan' => $name,
                    'fitur' => 'keg_warga',
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }
        DB::table('statuses')->where('fitur', 'keg_warga')->delete();
    }
};
