<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }

        if (! Schema::hasColumn('statuses', 'status_id')) {
            Schema::table('statuses', function (Blueprint $table) {
                $table->string('status_id', 32)->nullable()->after('id');
            });
        }

        $rows = DB::table('statuses')->orderBy('id')->get(['id']);
        $seq = 0;
        foreach ($rows as $row) {
            $seq++;
            $newId = 'STS-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            DB::table('statuses')->where('id', $row->id)->update(['status_id' => $newId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }
        if (Schema::hasColumn('statuses', 'status_id')) {
            Schema::table('statuses', function (Blueprint $table) {
                $table->dropColumn('status_id');
            });
        }
    }
};
