<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('keg_karang_taruna')) {
            Schema::table('keg_karang_taruna', function (Blueprint $table) {
                if (Schema::hasColumn('keg_karang_taruna', 'status_kegiatan')) {
                    $table->dropColumn('status_kegiatan');
                }
                if (Schema::hasColumn('keg_karang_taruna', 'status_id_new')) {
                    $table->dropColumn('status_id_new');
                }
            });
        }

        if (Schema::hasTable('keg_kesehatans')) {
            try {
                $fk = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->whereRaw('TABLE_SCHEMA = DATABASE()')
                    ->where('TABLE_NAME', 'keg_kesehatans')
                    ->where('COLUMN_NAME', 'status_id')
                    ->whereNotNull('REFERENCED_TABLE_NAME')
                    ->value('CONSTRAINT_NAME');
                if ($fk) {
                    DB::statement("ALTER TABLE `keg_kesehatans` DROP FOREIGN KEY `{$fk}`");
                }
            } catch (\Throwable $e) {
            }
            Schema::table('keg_kesehatans', function (Blueprint $table) {
                if (Schema::hasColumn('keg_kesehatans', 'status_id')) {
                    $table->dropColumn('status_id');
                }
            });
        }
    }

    public function down(): void {}
};
