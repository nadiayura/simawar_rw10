<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing FKs referencing statuses.id
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('REFERENCED_TABLE_NAME', 'statuses')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->get(['TABLE_NAME', 'CONSTRAINT_NAME']);
        foreach ($refs as $ref) {
            Schema::table($ref->TABLE_NAME, function (Blueprint $table) use ($ref) {
                try {
                    $table->dropForeign($ref->CONSTRAINT_NAME);
                } catch (\Throwable $e) {
                }
            });
        }

        // Convert status_id columns to string and backfill
        $tables = ['pengaduan', 'surat_ket_wargas', 'tagihan_iuran_wargas', 'pembayaran_midtrans'];
        foreach ($tables as $tbl) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                if (! Schema::hasColumn($tbl, 'status_id_new')) {
                    $table->string('status_id_new', 32)->nullable()->after('status_id');
                }
            });

            DB::statement("UPDATE `{$tbl}` t JOIN statuses s ON t.status_id = s.id SET t.status_id_new = s.status_id WHERE t.status_id IS NOT NULL");

            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                if (Schema::hasColumn($tbl, 'status_id')) {
                    $table->dropColumn('status_id');
                }
            });

            Schema::table($tbl, function (Blueprint $table) {
                $table->renameColumn('status_id_new', 'status_id');
            });

            try {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->foreign('status_id')->references('status_id')->on('statuses')->onDelete('restrict');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void
    {
        $tables = ['pengaduan', 'surat_ket_wargas', 'tagihan_iuran_wargas', 'pembayaran_midtrans'];
        foreach ($tables as $tbl) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                try {
                    $table->dropForeign([$tbl.'_status_id_foreign']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex(['status_id']);
                } catch (\Throwable $e) {
                }
                if (! Schema::hasColumn($tbl, 'status_id_old')) {
                    $table->unsignedBigInteger('status_id_old')->nullable()->after('status_id');
                }
            });
            DB::statement("UPDATE `{$tbl}` t JOIN statuses s ON t.status_id = s.status_id SET t.status_id_old = s.id WHERE t.status_id IS NOT NULL");
            Schema::table($tbl, function (Blueprint $table) {
                $table->dropColumn('status_id');
                $table->renameColumn('status_id_old', 'status_id');
            });
        }
    }
};
