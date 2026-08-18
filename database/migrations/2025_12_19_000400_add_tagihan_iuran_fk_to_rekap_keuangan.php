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
            if (! Schema::hasColumn('rekap_keuangan', 'tagihan_iuran_id')) {
                $table->string('tagihan_iuran_id', 150)->nullable();
            }
        });

        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'rekap_keuangan')
            ->where('COLUMN_NAME', 'tagihan_iuran_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if (! $fkExists && Schema::hasTable('tagihan_iuran_wargas') && Schema::hasColumn('tagihan_iuran_wargas', 'tagihan_iuran_id')) {
            Schema::table('rekap_keuangan', function (Blueprint $table) {
                $table->foreign('tagihan_iuran_id')
                    ->references('tagihan_iuran_id')
                    ->on('tagihan_iuran_wargas')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('rekap_keuangan')) {
            return;
        }

        $constraint = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'rekap_keuangan')
            ->where('COLUMN_NAME', 'tagihan_iuran_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        Schema::table('rekap_keuangan', function (Blueprint $table) use ($constraint) {
            if ($constraint) {
                $table->dropForeign($constraint);
            }
            if (Schema::hasColumn('rekap_keuangan', 'tagihan_iuran_id')) {
                $table->dropColumn('tagihan_iuran_id');
            }
        });
    }
};
