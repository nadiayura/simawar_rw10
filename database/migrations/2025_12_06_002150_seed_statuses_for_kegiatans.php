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

        $insertIfMissing = function (string $fitur, array $names) use ($now) {
            foreach ($names as $name) {
                $exists = DB::table('statuses')
                    ->where('fitur', $fitur)
                    ->whereRaw('LOWER(keterangan) = ?', [strtolower($name)])
                    ->exists();
                if (! $exists) {
                    $id = strtoupper(substr($fitur, 0, 3)).'-'.strtoupper(substr($name, 0, 3)).'-'.substr(md5($fitur.$name.$now), 0, 6);
                    DB::table('statuses')->insert([
                        'status_id' => $id,
                        'keterangan' => $name,
                        'fitur' => $fitur,
                    ]);
                }
            }
        };

        $insertIfMissing('keg_kesehatan', ['Terjadwal', 'Selesai', 'Dibatalkan']);
        $insertIfMissing('keg_karang_taruna', ['Terjadwal', 'Diajukan', 'Diproses', 'Disetujui', 'Ditolak', 'Selesai']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }
        DB::table('statuses')->whereIn('fitur', ['keg_kesehatan', 'keg_karang_taruna'])->delete();
    }
};
