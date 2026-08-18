<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('no_rts')) {
            return;
        }

        if (! Schema::hasColumn('no_rts', 'no_rt_id')) {
            Schema::table('no_rts', function (Blueprint $table) {
                $table->string('no_rt_id', 32)->nullable()->after('id');
            });
        }

        $rows = DB::table('no_rts')->orderBy('id')->get(['id']);
        $seq = 0;
        foreach ($rows as $row) {
            $seq++;
            $newId = 'RT-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            DB::table('no_rts')->where('id', $row->id)->update(['no_rt_id' => $newId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('no_rts')) {
            return;
        }
        if (Schema::hasColumn('no_rts', 'no_rt_id')) {
            Schema::table('no_rts', function (Blueprint $table) {
                $table->dropColumn('no_rt_id');
            });
        }
    }
};
