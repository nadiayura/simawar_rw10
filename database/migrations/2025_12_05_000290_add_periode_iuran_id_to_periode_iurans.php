<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('periode_iurans')) {
            return;
        }

        if (! Schema::hasColumn('periode_iurans', 'periode_iuran_id')) {
            Schema::table('periode_iurans', function (Blueprint $table) {
                $table->string('periode_iuran_id', 32)->nullable()->after('id');
            });
        }

        $rows = DB::table('periode_iurans')->get(['id', 'tahun', 'bulan']);
        foreach ($rows as $row) {
            $tahun = (int) ($row->tahun ?? now()->year);
            $bulan = (int) ($row->bulan ?? 1);
            $newId = 'IURAN-'.str_pad((string) $tahun, 4, '0', STR_PAD_LEFT).'-'.str_pad((string) $bulan, 2, '0', STR_PAD_LEFT);
            DB::table('periode_iurans')->where('id', $row->id)->update(['periode_iuran_id' => $newId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('periode_iurans')) {
            return;
        }
        if (Schema::hasColumn('periode_iurans', 'periode_iuran_id')) {
            Schema::table('periode_iurans', function (Blueprint $table) {
                $table->dropColumn('periode_iuran_id');
            });
        }
    }
};
