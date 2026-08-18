<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas') || ! Schema::hasTable('iurans')) {
            return;
        }

        if (
            ! Schema::hasColumn('tagihan_iuran_wargas', 'tagihan_iuran_id')
            || ! Schema::hasColumn('tagihan_iuran_wargas', 'iuran_id')
            || ! Schema::hasColumn('tagihan_iuran_wargas', 'nominal_tagihan')
        ) {
            return;
        }

        if (! Schema::hasColumn('iurans', 'iuran_id') || ! Schema::hasColumn('iurans', 'jumlah_default')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'UPDATE tagihan_iuran_wargas t
                JOIN iurans i ON i.iuran_id = t.iuran_id
                SET t.nominal_tagihan = i.jumlah_default
                WHERE t.nominal_tagihan IS NULL OR t.nominal_tagihan = 0'
            );

            return;
        }

        $rows = DB::table('tagihan_iuran_wargas')
            ->select(['tagihan_iuran_id', 'iuran_id'])
            ->whereNull('nominal_tagihan')
            ->orWhere('nominal_tagihan', '=', 0)
            ->get();

        foreach ($rows as $row) {
            $amount = DB::table('iurans')
                ->where('iuran_id', $row->iuran_id)
                ->value('jumlah_default');

            if ($amount === null) {
                continue;
            }

            DB::table('tagihan_iuran_wargas')
                ->where('tagihan_iuran_id', $row->tagihan_iuran_id)
                ->update(['nominal_tagihan' => $amount]);
        }
    }

    public function down(): void {}
};
