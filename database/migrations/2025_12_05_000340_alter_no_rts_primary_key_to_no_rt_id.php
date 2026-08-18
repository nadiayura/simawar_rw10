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

        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('REFERENCED_TABLE_NAME', 'no_rts')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->get(['TABLE_NAME', 'CONSTRAINT_NAME']);
        foreach ($refs as $ref) {
            Schema::table($ref->TABLE_NAME, function (Blueprint $table) use ($ref) {
                $table->dropForeign($ref->CONSTRAINT_NAME);
            });
        }

        DB::statement('ALTER TABLE no_rts MODIFY no_rt_id VARCHAR(32) NOT NULL');
        DB::statement('ALTER TABLE no_rts MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE no_rts DROP PRIMARY KEY, ADD PRIMARY KEY (no_rt_id)');

        Schema::table('no_rts', function (Blueprint $table) {
            if (Schema::hasColumn('no_rts', 'id')) {
                $table->dropColumn('id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('no_rts')) {
            return;
        }
        Schema::table('no_rts', function (Blueprint $table) {
            $table->dropPrimary();
            $table->bigIncrements('id')->first();
        });
        // Keep no_rt_id column
    }
};
