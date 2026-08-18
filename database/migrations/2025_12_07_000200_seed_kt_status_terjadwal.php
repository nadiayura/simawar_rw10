<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function nextSeq(): string
    {
        $max = DB::table('statuses')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(status_id, '-', -1) AS UNSIGNED)) AS m")
            ->whereRaw("status_id LIKE 'STS-%'")
            ->value('m');
        $n = ((int) ($max ?? 0)) + 1;

        return 'STS-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT);
    }

    public function up(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }

        $exists = DB::table('statuses')
            ->where('fitur', 'keg_warga')
            ->whereRaw('LOWER(keterangan) = ?', ['terjadwal'])
            ->exists();
        if (! $exists) {
            DB::table('statuses')->insert([
                'status_id' => $this->nextSeq(),
                'keterangan' => 'Terjadwal',
                'fitur' => 'keg_warga',
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }
        DB::table('statuses')
            ->where('fitur', 'keg_karang_taruna')
            ->where('keterangan', 'Terjadwal')
            ->delete();
    }
};
