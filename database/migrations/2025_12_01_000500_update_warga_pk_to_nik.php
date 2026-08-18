<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add warga_nik columns to related tables
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'warga_nik')) {
                $table->string('warga_nik', 16)->nullable()->after('warga_id');
            }
        });

        Schema::table('pengaduan', function (Blueprint $table) {
            if (! Schema::hasColumn('pengaduan', 'warga_nik')) {
                $table->string('warga_nik', 16)->nullable()->after('id_warga');
            }
        });

        Schema::table('ketua_rts', function (Blueprint $table) {
            if (! Schema::hasColumn('ketua_rts', 'warga_nik')) {
                $table->string('warga_nik', 16)->nullable()->after('id_warga');
            }
        });

        Schema::table('strukturals', function (Blueprint $table) {
            if (! Schema::hasColumn('strukturals', 'warga_nik')) {
                $table->string('warga_nik', 16)->nullable()->after('id_warga');
            }
        });

        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_ket_wargas', 'warga_nik')) {
                $table->string('warga_nik', 16)->nullable()->after('id_warga');
            }
        });

        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('tagihan_iuran_wargas', 'warga_nik')) {
                $table->string('warga_nik', 16)->nullable()->after('warga_id');
            }
        });

        // 2) Backfill warga_nik from existing FK to wargas.id (only if source columns exist)
        if (Schema::hasColumn('users', 'warga_id')) {
            DB::statement('UPDATE users u JOIN wargas w ON u.warga_id = w.id SET u.warga_nik = w.nik WHERE u.warga_id IS NOT NULL');
        }
        if (Schema::hasColumn('pengaduan', 'id_warga')) {
            DB::statement('UPDATE pengaduan p JOIN wargas w ON p.id_warga = w.id SET p.warga_nik = w.nik WHERE p.id_warga IS NOT NULL');
        }
        if (Schema::hasColumn('ketua_rts', 'id_warga')) {
            DB::statement('UPDATE ketua_rts k JOIN wargas w ON k.id_warga = w.id SET k.warga_nik = w.nik WHERE k.id_warga IS NOT NULL');
        }
        if (Schema::hasColumn('strukturals', 'id_warga')) {
            DB::statement('UPDATE strukturals s JOIN wargas w ON s.id_warga = w.id SET s.warga_nik = w.nik WHERE s.id_warga IS NOT NULL');
        }
        if (Schema::hasColumn('surat_ket_wargas', 'id_warga')) {
            DB::statement('UPDATE surat_ket_wargas s JOIN wargas w ON s.id_warga = w.id SET s.warga_nik = w.nik WHERE s.id_warga IS NOT NULL');
        }
        if (Schema::hasColumn('tagihan_iuran_wargas', 'warga_id')) {
            DB::statement('UPDATE tagihan_iuran_wargas t JOIN wargas w ON t.warga_id = w.id SET t.warga_nik = w.nik WHERE t.warga_id IS NOT NULL');
        }

        // 3) Drop old FK constraints and columns
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'warga_id')) {
                $table->dropForeign(['warga_id']);
                $table->dropColumn('warga_id');
            }
        });

        Schema::table('pengaduan', function (Blueprint $table) {
            if (Schema::hasColumn('pengaduan', 'id_warga')) {
                $table->dropForeign(['id_warga']);
                $table->dropColumn('id_warga');
            }
        });

        Schema::table('ketua_rts', function (Blueprint $table) {
            if (Schema::hasColumn('ketua_rts', 'id_warga')) {
                $table->dropForeign(['id_warga']);
                $table->dropColumn('id_warga');
            }
        });

        Schema::table('strukturals', function (Blueprint $table) {
            if (Schema::hasColumn('strukturals', 'id_warga')) {
                $table->dropForeign(['id_warga']);
                $table->dropColumn('id_warga');
            }
        });

        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (Schema::hasColumn('surat_ket_wargas', 'id_warga')) {
                $table->dropForeign(['id_warga']);
                $table->dropColumn('id_warga');
            }
        });

        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            if (Schema::hasColumn('tagihan_iuran_wargas', 'warga_id')) {
                $table->dropForeign(['warga_id']);
                $table->dropColumn('warga_id');
            }
        });

        // 4) Add new FK constraints to wargas.nik (ensure no duplicate FK names)
        $dropIfExists = function (string $table, string $fkName) {
            $exists = DB::select('SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?', [$table, $fkName]);
            if (! empty($exists)) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
            }
        };

        $dropIfExists('users', 'users_warga_nik_foreign');
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'warga_nik')) {
                $table->foreign('warga_nik')->references('nik')->on('wargas')->onDelete('set null');
            }
        });

        $dropIfExists('pengaduan', 'pengaduan_warga_nik_foreign');
        Schema::table('pengaduan', function (Blueprint $table) {
            if (Schema::hasColumn('pengaduan', 'warga_nik')) {
                $table->foreign('warga_nik')->references('nik')->on('wargas')->onDelete('cascade');
            }
        });

        $dropIfExists('ketua_rts', 'ketua_rts_warga_nik_foreign');
        Schema::table('ketua_rts', function (Blueprint $table) {
            if (Schema::hasColumn('ketua_rts', 'warga_nik')) {
                $table->foreign('warga_nik')->references('nik')->on('wargas')->onDelete('cascade');
            }
        });

        $dropIfExists('strukturals', 'strukturals_warga_nik_foreign');
        Schema::table('strukturals', function (Blueprint $table) {
            if (Schema::hasColumn('strukturals', 'warga_nik')) {
                $table->foreign('warga_nik')->references('nik')->on('wargas')->onDelete('set null');
            }
        });

        $dropIfExists('surat_ket_wargas', 'surat_ket_wargas_warga_nik_foreign');
        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (Schema::hasColumn('surat_ket_wargas', 'warga_nik')) {
                $table->foreign('warga_nik')->references('nik')->on('wargas')->onDelete('cascade');
            }
        });

        $dropIfExists('tagihan_iuran_wargas', 'tagihan_iuran_wargas_warga_nik_foreign');
        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            if (Schema::hasColumn('tagihan_iuran_wargas', 'warga_nik')) {
                $table->foreign('warga_nik')->references('nik')->on('wargas')->onDelete('cascade');
            }
        });

        // 5) Make nik the PRIMARY KEY of wargas
        // Remove AUTO_INCREMENT requirement on id before switching PK
        DB::statement('ALTER TABLE wargas MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE wargas MODIFY nik VARCHAR(16) NOT NULL');
        DB::statement('ALTER TABLE wargas DROP PRIMARY KEY, ADD PRIMARY KEY (nik)');
        // Keep old id column for reference but not as PK
    }

    public function down(): void
    {
        // Reverse process: drop new FKs and warga_nik columns, restore warga_id/id_warga and PK id

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['warga_nik']);
            $table->dropColumn('warga_nik');
            $table->foreignId('warga_id')->nullable()->constrained('wargas')->onDelete('set null');
        });

        Schema::table('pengaduan', function (Blueprint $table) {
            $table->dropForeign(['warga_nik']);
            $table->dropColumn('warga_nik');
            $table->foreignId('id_warga')->constrained('wargas')->onDelete('cascade');
        });

        Schema::table('ketua_rts', function (Blueprint $table) {
            $table->dropForeign(['warga_nik']);
            $table->dropColumn('warga_nik');
            $table->foreignId('id_warga')->constrained('wargas')->onDelete('cascade');
        });

        Schema::table('strukturals', function (Blueprint $table) {
            $table->dropForeign(['warga_nik']);
            $table->dropColumn('warga_nik');
            $table->unsignedBigInteger('id_warga')->nullable();
            $table->foreign('id_warga')->references('id')->on('wargas')->onDelete('set null');
        });

        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            $table->dropForeign(['warga_nik']);
            $table->dropColumn('warga_nik');
            $table->foreignId('id_warga')->constrained('wargas')->onDelete('cascade');
        });

        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            $table->dropForeign(['warga_nik']);
            $table->dropColumn('warga_nik');
            $table->foreignId('warga_id')->constrained('wargas')->onDelete('cascade');
        });

        // Restore PK id on wargas with AUTO_INCREMENT
        DB::statement('ALTER TABLE wargas DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        DB::statement('ALTER TABLE wargas MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }
};
