<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wargas')) {
            return;
        }

        // 1) Tambah kolom warga_nik baru (string) untuk menampung format WRG-<nik>
        if (! Schema::hasColumn('wargas', 'warga_nik')) {
            Schema::table('wargas', function (Blueprint $table) {
                $table->string('warga_nik', 32)->nullable()->after('nik');
            });
        }

        // 2) Backfill warga_nik dari nik dengan prefix WRG-
        DB::statement("UPDATE wargas SET warga_nik = CONCAT('WRG-', nik) WHERE nik IS NOT NULL AND warga_nik IS NULL");

        // 3) Drop semua foreign key yang mereferensikan wargas.nik
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('REFERENCED_TABLE_NAME', 'wargas')
            ->where('REFERENCED_COLUMN_NAME', 'nik')
            ->get(['TABLE_NAME', 'CONSTRAINT_NAME']);
        foreach ($refs as $ref) {
            Schema::table($ref->TABLE_NAME, function (Blueprint $table) use ($ref) {
                try {
                    $table->dropForeign($ref->CONSTRAINT_NAME);
                } catch (\Throwable $e) {
                }
            });
        }

        // 4) Update nilai di tabel referensi agar ikut memakai prefix WRG-
        // Pastikan kolom FK bertipe VARCHAR(32) agar tidak terpotong
        $ensureString32 = function (string $table, string $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                try {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(32) NULL");
                } catch (\Throwable $e) {
                }
            }
        };
        $ensureString32('users', 'warga_nik');
        $ensureString32('pengaduan', 'warga_nik');
        $ensureString32('ketua_rts', 'warga_nik');
        $ensureString32('strukturals', 'warga_nik');
        $ensureString32('surat_ket_wargas', 'warga_nik');
        $ensureString32('tagihan_iuran_wargas', 'warga_nik');

        $updateFk = function (string $table, string $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::statement("UPDATE `{$table}` t JOIN wargas w ON t.`{$column}` = w.nik SET t.`{$column}` = w.warga_nik WHERE t.`{$column}` IS NOT NULL");
            }
        };
        $updateFk('users', 'warga_nik');
        $updateFk('pengaduan', 'warga_nik');
        $updateFk('ketua_rts', 'warga_nik');
        $updateFk('strukturals', 'warga_nik');
        $updateFk('surat_ket_wargas', 'warga_nik');
        $updateFk('tagihan_iuran_wargas', 'warga_nik');

        // 5) Jadikan warga_nik NOT NULL dan set sebagai PRIMARY KEY, hapus kolom nik
        DB::statement('ALTER TABLE wargas MODIFY warga_nik VARCHAR(32) NOT NULL');
        // Remove AUTO_INCREMENT requirement on id before switching PK if needed
        DB::statement('ALTER TABLE wargas DROP PRIMARY KEY, ADD PRIMARY KEY (warga_nik)');
        Schema::table('wargas', function (Blueprint $table) {
            if (Schema::hasColumn('wargas', 'nik')) {
                $table->dropColumn('nik');
            }
        });

        // 6) Tambahkan kembali FK ke wargas.warga_nik
        $addFk = function (string $table, string $column, string $onDelete = 'cascade') {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                try {
                    Schema::table($table, function (Blueprint $table) use ($column, $onDelete) {
                        $table->foreign($column)->references('warga_nik')->on('wargas')->onDelete($onDelete);
                    });
                } catch (\Throwable $e) {
                }
            }
        };
        $addFk('users', 'warga_nik', 'set null');
        $addFk('pengaduan', 'warga_nik', 'cascade');
        $addFk('ketua_rts', 'warga_nik', 'cascade');
        $addFk('strukturals', 'warga_nik', 'set null');
        $addFk('surat_ket_wargas', 'warga_nik', 'cascade');
        $addFk('tagihan_iuran_wargas', 'warga_nik', 'cascade');
    }

    public function down(): void
    {
        if (! Schema::hasTable('wargas')) {
            return;
        }

        // Best-effort rollback: tambahkan kembali kolom nik dan isi tanpa prefix
        if (! Schema::hasColumn('wargas', 'nik')) {
            Schema::table('wargas', function (Blueprint $table) {
                $table->string('nik', 16)->nullable()->after('warga_nik');
            });
        }
        DB::statement("UPDATE wargas SET nik = REPLACE(warga_nik, 'WRG-', '') WHERE warga_nik IS NOT NULL");

        // Drop FK baru
        $tables = ['users', 'pengaduan', 'ketua_rts', 'strukturals', 'surat_ket_wargas', 'tagihan_iuran_wargas'];
        foreach ($tables as $tbl) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }
            $exists = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', $tbl)
                ->where('COLUMN_NAME', 'warga_nik')
                ->where('REFERENCED_TABLE_NAME', 'wargas')
                ->where('REFERENCED_COLUMN_NAME', 'warga_nik')
                ->get(['CONSTRAINT_NAME']);
            foreach ($exists as $ref) {
                Schema::table($tbl, function (Blueprint $table) use ($ref) {
                    try {
                        $table->dropForeign($ref->CONSTRAINT_NAME);
                    } catch (\Throwable $e) {
                    }
                });
            }
        }

        // Kembalikan PK ke nik
        DB::statement('ALTER TABLE wargas DROP PRIMARY KEY, ADD PRIMARY KEY (nik)');

        // Kembalikan nilai FK di tabel lain ke nik
        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'warga_nik')) {
                DB::statement("UPDATE `{$tbl}` t JOIN wargas w ON t.warga_nik = w.warga_nik SET t.warga_nik = w.nik WHERE t.warga_nik IS NOT NULL");
            }
        }

        // Tambahkan FK lama ke wargas.nik
        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'warga_nik')) {
                try {
                    Schema::table($tbl, function (Blueprint $table) {
                        $table->foreign('warga_nik')->references('nik')->on('wargas')->onDelete('cascade');
                    });
                } catch (\Throwable $e) {
                }
            }
        }

        // Kembalikan ukuran kolom FK ke VARCHAR(16) jika diperlukan
        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'warga_nik')) {
                try {
                    DB::statement("ALTER TABLE `{$tbl}` MODIFY `warga_nik` VARCHAR(16) NULL");
                } catch (\Throwable $e) {
                }
            }
        }
    }
};
