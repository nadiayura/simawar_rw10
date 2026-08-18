<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas')) {
            return;
        }

        $fkList = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'tagihan_iuran_wargas')
            ->where('COLUMN_NAME', 'periode_id')
            ->get(['CONSTRAINT_NAME']);
        foreach ($fkList as $fk) {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) use ($fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            });
        }

        if (! Schema::hasColumn('tagihan_iuran_wargas', 'periode_id_new')) {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
                $table->string('periode_id_new', 32)->nullable()->after('iuran_id');
            });
        }

        DB::statement('UPDATE tagihan_iuran_wargas t JOIN periode_iurans p ON t.periode_id = p.id SET t.periode_id_new = p.periode_iuran_id');

        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            $table->dropColumn('periode_id');
        });
        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            $table->renameColumn('periode_id_new', 'periode_id');
        });

        try {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
                $table->foreign('periode_id')->references('periode_iuran_id')->on('periode_iurans')->onDelete('restrict');
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas')) {
            return;
        }

        $fkList = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'tagihan_iuran_wargas')
            ->where('COLUMN_NAME', 'periode_id')
            ->get(['CONSTRAINT_NAME']);
        foreach ($fkList as $fk) {
            Schema::table('tagihan_iuran_wargas', function (Blueprint $table) use ($fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            });
        }

        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            $table->unsignedBigInteger('periode_id')->nullable()->after('iuran_id');
        });
    }
};
