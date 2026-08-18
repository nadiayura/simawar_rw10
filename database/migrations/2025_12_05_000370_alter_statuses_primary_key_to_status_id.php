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

        // Ensure status_id is NOT NULL
        DB::statement('ALTER TABLE statuses MODIFY status_id VARCHAR(32) NOT NULL');

        // Remove AUTO_INCREMENT on id and switch PK
        DB::statement('ALTER TABLE statuses MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE statuses DROP PRIMARY KEY, ADD PRIMARY KEY (status_id)');

        // Drop old id column
        Schema::table('statuses', function (Blueprint $table) {
            if (Schema::hasColumn('statuses', 'id')) {
                $table->dropColumn('id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropPrimary();
            $table->bigIncrements('id')->first();
        });
        // Keep status_id column
    }
};
