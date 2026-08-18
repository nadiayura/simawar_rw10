<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah FK ke wargas
        Schema::table('wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('wargas', 'no_rt_id')) {
                $table->foreignId('no_rt_id')
                    ->nullable()
                    ->constrained('no_rts')
                    ->nullOnDelete()
                    ->after('id');
                $table->index('no_rt_id');
            }
        });

        // Tambah FK ke ketua_rts dan sesuaikan unique constraint
        Schema::table('ketua_rts', function (Blueprint $table) {
            // Drop unique lama berbasis string no_rt + jabatan + is_active
            $table->dropUnique('unique_rt_jabatan_active');

            if (! Schema::hasColumn('ketua_rts', 'no_rt_id')) {
                $table->foreignId('no_rt_id')
                    ->nullable()
                    ->constrained('no_rts')
                    ->cascadeOnDelete()
                    ->after('no_rt');
                $table->index('no_rt_id');
            }

            // Unique baru menggunakan FK no_rt_id
            $table->unique(['no_rt_id', 'jabatan', 'is_active'], 'unique_no_rt_id_jabatan_active');
        });
    }

    public function down(): void
    {
        // Kembalikan perubahan di ketua_rts
        Schema::table('ketua_rts', function (Blueprint $table) {
            $table->dropUnique('unique_no_rt_id_jabatan_active');

            if (Schema::hasColumn('ketua_rts', 'no_rt_id')) {
                $table->dropForeign(['no_rt_id']);
                $table->dropIndex(['no_rt_id']);
                $table->dropColumn('no_rt_id');
            }

            // Pulihkan unique lama
            $table->unique(['no_rt', 'jabatan', 'is_active'], 'unique_rt_jabatan_active');
        });

        // Kembalikan perubahan di wargas
        Schema::table('wargas', function (Blueprint $table) {
            if (Schema::hasColumn('wargas', 'no_rt_id')) {
                $table->dropForeign(['no_rt_id']);
                $table->dropIndex(['no_rt_id']);
                $table->dropColumn('no_rt_id');
            }
        });
    }
};
