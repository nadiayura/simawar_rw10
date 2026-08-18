<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('iurans')) {
            return;
        }

        if (! Schema::hasColumn('iurans', 'iuran_id')) {
            Schema::table('iurans', function (Blueprint $table) {
                $table->string('iuran_id', 32)->nullable()->after('id');
            });
        }

        $rows = DB::table('iurans')->orderBy('id')->get(['id']);
        $seq = 0;
        foreach ($rows as $row) {
            $seq++;
            $newId = 'IURAN-JNS-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            DB::table('iurans')->where('id', $row->id)->update(['iuran_id' => $newId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('iurans')) {
            return;
        }
        if (Schema::hasColumn('iurans', 'iuran_id')) {
            Schema::table('iurans', function (Blueprint $table) {
                $table->dropColumn('iuran_id');
            });
        }
    }
};
