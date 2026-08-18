<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('no_rts')) {
            return;
        }

        $map = [
            'RT-007' => 'RT-001',
            'RT-008' => 'RT-002',
            'RT-009' => 'RT-003',
            'RT-010' => 'RT-004',
            'RT-011' => 'RT-005',
            'RT-012' => 'RT-006',
        ];

        $tables = [
            'wargas',
            'ketua_rts',
            'rekap_keuangan',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'no_rt_id')) {
                continue;
            }
            foreach ($map as $from => $to) {
                DB::table($table)->where('no_rt_id', $from)->update(['no_rt_id' => $to]);
            }
        }

        DB::table('no_rts')->whereIn('no_rt_id', array_keys($map))->delete();
    }

    public function down(): void
    {
        // Data migration is irreversible
    }
};
