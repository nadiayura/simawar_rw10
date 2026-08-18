<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rekap_keuangan')) {
            return;
        }

        Schema::table('rekap_keuangan', function (Blueprint $table) {
            if (! Schema::hasColumn('rekap_keuangan', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('bukti');
            }
            if (! Schema::hasColumn('rekap_keuangan', 'no_rt_id')) {
                $table->string('no_rt_id', 32)->nullable()->after('keterangan');
            }
        });

        try {
            $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'rekap_keuangan')
                ->where('COLUMN_NAME', 'no_rt_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->exists();

            if (! $fkExists && Schema::hasTable('no_rts') && Schema::hasColumn('no_rts', 'no_rt_id')) {
                Schema::table('rekap_keuangan', function (Blueprint $table) {
                    $table->foreign('no_rt_id')
                        ->references('no_rt_id')
                        ->on('no_rts')
                        ->nullOnDelete();
                });
            }
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('rekap_keuangan')) {
            return;
        }

        try {
            $fk = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'rekap_keuangan')
                ->where('COLUMN_NAME', 'no_rt_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->value('CONSTRAINT_NAME');
            if ($fk) {
                DB::statement("ALTER TABLE `rekap_keuangan` DROP FOREIGN KEY `{$fk}`");
            }
        } catch (\Throwable $e) {
        }

        Schema::table('rekap_keuangan', function (Blueprint $table) {
            if (Schema::hasColumn('rekap_keuangan', 'no_rt_id')) {
                $table->dropColumn('no_rt_id');
            }
            if (Schema::hasColumn('rekap_keuangan', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });
    }
};
