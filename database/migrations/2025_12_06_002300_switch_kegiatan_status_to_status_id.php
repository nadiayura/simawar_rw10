<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keg Kesehatans
        if (Schema::hasTable('keg_kesehatans')) {
            // Drop FKs proactively if they exist
            try {
                $fk = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->whereRaw('TABLE_SCHEMA = DATABASE()')
                    ->where('TABLE_NAME', 'keg_kesehatans')
                    ->where('COLUMN_NAME', 'status_kegiatan')
                    ->whereNotNull('REFERENCED_TABLE_NAME')
                    ->value('CONSTRAINT_NAME');
                if ($fk) {
                    DB::statement("ALTER TABLE `keg_kesehatans` DROP FOREIGN KEY `{$fk}`");
                }
            } catch (\Throwable $e) {
            }
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
                if (! Schema::hasColumn('keg_kesehatans', 'status_id_new')) {
                    $table->string('status_id_new', 32)->nullable()->after('dokumentasi');
                }
            });

            if (Schema::hasColumn('keg_kesehatans', 'status_kegiatan')) {
                DB::statement('UPDATE keg_kesehatans k SET k.status_id_new = k.status_kegiatan WHERE k.status_kegiatan IS NOT NULL');
                // Fallback by name
                DB::statement("UPDATE keg_kesehatans k JOIN statuses s ON LOWER(k.status_kegiatan) = LOWER(s.keterangan) AND s.fitur = 'kegiatan' SET k.status_id_new = s.status_id WHERE k.status_id_new IS NULL");
            } elseif (Schema::hasColumn('keg_kesehatans', 'status_id')) {
                // Fallback: no numeric mapping available, set default for fitur keg_kesehatan
                $default = DB::table('statuses')
                    ->where('fitur', 'kegiatan')
                    ->whereRaw('LOWER(keterangan) = ?', ['selesai'])
                    ->value('status_id');
                if ($default) {
                    DB::table('keg_kesehatans')
                        ->whereNull('status_id_new')
                        ->update(['status_id_new' => $default]);
                }
            }

            Schema::table('keg_kesehatans', function (Blueprint $table) {
                if (Schema::hasColumn('keg_kesehatans', 'status_kegiatan')) {
                    $table->dropColumn('status_kegiatan');
                }
                if (Schema::hasColumn('keg_kesehatans', 'status_id')) {
                    $table->dropColumn('status_id');
                }
            });

            Schema::table('keg_kesehatans', function (Blueprint $table) {
                $table->renameColumn('status_id_new', 'status_id');
            });

            try {
                Schema::table('keg_kesehatans', function (Blueprint $table) {
                    $table->foreign('status_id')->references('status_id')->on('statuses')->onDelete('restrict');
                });
            } catch (\Throwable $e) {
            }
        }

        // Keg Karang Taruna
        if (Schema::hasTable('keg_karang_taruna')) {
            // Drop FKs proactively if they exist
            try {
                $fk = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->whereRaw('TABLE_SCHEMA = DATABASE()')
                    ->where('TABLE_NAME', 'keg_karang_taruna')
                    ->where('COLUMN_NAME', 'status_kegiatan')
                    ->whereNotNull('REFERENCED_TABLE_NAME')
                    ->value('CONSTRAINT_NAME');
                if ($fk) {
                    DB::statement("ALTER TABLE `keg_karang_taruna` DROP FOREIGN KEY `{$fk}`");
                }
            } catch (\Throwable $e) {
            }
            try {
                $fk = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->whereRaw('TABLE_SCHEMA = DATABASE()')
                    ->where('TABLE_NAME', 'keg_karang_taruna')
                    ->where('COLUMN_NAME', 'status_id')
                    ->whereNotNull('REFERENCED_TABLE_NAME')
                    ->value('CONSTRAINT_NAME');
                if ($fk) {
                    DB::statement("ALTER TABLE `keg_karang_taruna` DROP FOREIGN KEY `{$fk}`");
                }
            } catch (\Throwable $e) {
            }
            Schema::table('keg_karang_taruna', function (Blueprint $table) {
                if (! Schema::hasColumn('keg_karang_taruna', 'status_id_new')) {
                    $table->string('status_id_new', 32)->nullable()->after('penanggung_jawab');
                }
            });

            if (Schema::hasColumn('keg_karang_taruna', 'status_kegiatan')) {
                DB::statement('UPDATE keg_karang_taruna k SET k.status_id_new = k.status_kegiatan WHERE k.status_kegiatan IS NOT NULL');
                DB::statement("UPDATE keg_karang_taruna k JOIN statuses s ON LOWER(k.status_kegiatan) = LOWER(s.keterangan) AND s.fitur = 'kegiatan' SET k.status_id_new = s.status_id WHERE k.status_id_new IS NULL");
            } elseif (Schema::hasColumn('keg_karang_taruna', 'status_id')) {
                $default = DB::table('statuses')
                    ->where('fitur', 'kegiatan')
                    ->whereRaw('LOWER(keterangan) = ?', ['terjadwal'])
                    ->value('status_id');
                if ($default) {
                    DB::table('keg_karang_taruna')
                        ->whereNull('status_id_new')
                        ->update(['status_id_new' => $default]);
                }
            }

            Schema::table('keg_karang_taruna', function (Blueprint $table) {
                if (Schema::hasColumn('keg_karang_taruna', 'status_kegiatan')) {
                    $table->dropColumn('status_kegiatan');
                }
                if (Schema::hasColumn('keg_karang_taruna', 'status_id')) {
                    $table->dropColumn('status_id');
                }
            });

            Schema::table('keg_karang_taruna', function (Blueprint $table) {
                $table->renameColumn('status_id_new', 'status_id');
            });

            try {
                Schema::table('keg_karang_taruna', function (Blueprint $table) {
                    $table->foreign('status_id')->references('status_id')->on('statuses')->onDelete('restrict');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void
    {
        // No automatic rollback; switching columns is not trivially reversible.
    }
};
