<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('keg_kesehatans')) {
            return;
        }

        $rows = DB::table('keg_kesehatans')
            ->orderBy('created_at')
            ->orderBy('keg_kesehatan_id')
            ->get(['keg_kesehatan_id', 'tgl', 'created_at']);

        $tmpMap = [];
        foreach ($rows as $row) {
            $oldId = (string) $row->keg_kesehatan_id;
            $tmpId = 'KSHTN-TMP-'.uniqid('', true);
            DB::table('keg_kesehatans')->where('keg_kesehatan_id', $oldId)->update(['keg_kesehatan_id' => $tmpId]);
            $tmpMap[$oldId] = $tmpId;
        }

        $seq = 1;
        foreach ($rows as $row) {
            $baseDate = $row->tgl ? \Carbon\Carbon::parse($row->tgl) : ($row->created_at ? \Carbon\Carbon::parse($row->created_at) : now());
            $dateStr = $baseDate->format('dmY');
            $newId = 'KSHTN-'.$dateStr.'-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);

            $oldId = (string) $row->keg_kesehatan_id;
            $tmpId = $tmpMap[$oldId] ?? null;
            if ($tmpId) {
                DB::table('keg_kesehatans')->where('keg_kesehatan_id', $tmpId)->update(['keg_kesehatan_id' => $newId]);
            }
            $seq++;
        }
    }

    public function down(): void
    {
        // no-op
    }
};
