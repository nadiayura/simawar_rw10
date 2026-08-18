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

        // WARGAS: convert FK no_rt_id (bigint) -> string and relink to no_rts.no_rt_id
        if (Schema::hasTable('wargas')) {
            Schema::table('wargas', function (Blueprint $table) {
                try {
                    $table->dropForeign(['no_rt_id']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex(['no_rt_id']);
                } catch (\Throwable $e) {
                }
                if (! Schema::hasColumn('wargas', 'no_rt_id_new')) {
                    $table->string('no_rt_id_new', 32)->nullable()->after('no_rt_id');
                }
            });

            DB::statement('UPDATE wargas w JOIN no_rts n ON w.no_rt_id = n.id SET w.no_rt_id_new = n.no_rt_id WHERE w.no_rt_id IS NOT NULL');

            Schema::table('wargas', function (Blueprint $table) {
                if (Schema::hasColumn('wargas', 'no_rt_id')) {
                    $table->dropColumn('no_rt_id');
                }
            });

            Schema::table('wargas', function (Blueprint $table) {
                $table->renameColumn('no_rt_id_new', 'no_rt_id');
            });

            try {
                Schema::table('wargas', function (Blueprint $table) {
                    $table->index('no_rt_id');
                    $table->foreign('no_rt_id')->references('no_rt_id')->on('no_rts')->onDelete('set null');
                });
            } catch (\Throwable $e) {
            }
        }

        // KETUA_RTS: convert FK no_rt_id (bigint) -> string and relink to no_rts.no_rt_id
        if (Schema::hasTable('ketua_rts')) {
            Schema::table('ketua_rts', function (Blueprint $table) {
                try {
                    $table->dropUnique('unique_no_rt_id_jabatan_active');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropForeign(['no_rt_id']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex(['no_rt_id']);
                } catch (\Throwable $e) {
                }
                if (! Schema::hasColumn('ketua_rts', 'no_rt_id_new')) {
                    $table->string('no_rt_id_new', 32)->nullable()->after('no_rt_id');
                }
            });

            DB::statement('UPDATE ketua_rts k JOIN no_rts n ON k.no_rt_id = n.id SET k.no_rt_id_new = n.no_rt_id WHERE k.no_rt_id IS NOT NULL');

            Schema::table('ketua_rts', function (Blueprint $table) {
                if (Schema::hasColumn('ketua_rts', 'no_rt_id')) {
                    $table->dropColumn('no_rt_id');
                }
            });

            Schema::table('ketua_rts', function (Blueprint $table) {
                $table->renameColumn('no_rt_id_new', 'no_rt_id');
            });

            try {
                Schema::table('ketua_rts', function (Blueprint $table) {
                    $table->index('no_rt_id');
                    $table->foreign('no_rt_id')->references('no_rt_id')->on('no_rts')->onDelete('cascade');
                    $table->unique(['no_rt_id', 'jabatan', 'is_active'], 'unique_no_rt_id_jabatan_active');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void
    {
        // Best-effort rollback: convert back to bigint FK to no_rts.id if column exists
        if (Schema::hasTable('wargas')) {
            Schema::table('wargas', function (Blueprint $table) {
                try {
                    $table->dropForeign(['no_rt_id']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex(['no_rt_id']);
                } catch (\Throwable $e) {
                }
                if (! Schema::hasColumn('wargas', 'no_rt_id_old')) {
                    $table->unsignedBigInteger('no_rt_id_old')->nullable()->after('no_rt_id');
                }
            });
            DB::statement('UPDATE wargas w JOIN no_rts n ON w.no_rt_id = n.no_rt_id SET w.no_rt_id_old = n.id WHERE w.no_rt_id IS NOT NULL');
            Schema::table('wargas', function (Blueprint $table) {
                $table->dropColumn('no_rt_id');
                $table->renameColumn('no_rt_id_old', 'no_rt_id');
                $table->index('no_rt_id');
            });
        }

        if (Schema::hasTable('ketua_rts')) {
            Schema::table('ketua_rts', function (Blueprint $table) {
                try {
                    $table->dropUnique('unique_no_rt_id_jabatan_active');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropForeign(['no_rt_id']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex(['no_rt_id']);
                } catch (\Throwable $e) {
                }
                if (! Schema::hasColumn('ketua_rts', 'no_rt_id_old')) {
                    $table->unsignedBigInteger('no_rt_id_old')->nullable()->after('no_rt_id');
                }
            });
            DB::statement('UPDATE ketua_rts k JOIN no_rts n ON k.no_rt_id = n.no_rt_id SET k.no_rt_id_old = n.id WHERE k.no_rt_id IS NOT NULL');
            Schema::table('ketua_rts', function (Blueprint $table) {
                $table->dropColumn('no_rt_id');
                $table->renameColumn('no_rt_id_old', 'no_rt_id');
                $table->index('no_rt_id');
                $table->unique(['no_rt_id', 'jabatan', 'is_active'], 'unique_no_rt_id_jabatan_active');
            });
        }
    }
};
