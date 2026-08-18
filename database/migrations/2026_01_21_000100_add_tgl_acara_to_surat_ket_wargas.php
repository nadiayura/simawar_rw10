<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('surat_ket_wargas')) {
            return;
        }

        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_ket_wargas', 'tgl_acara_mulai')) {
                $table->date('tgl_acara_mulai')->nullable()->after('tgl_pengajuan');
            }
            if (! Schema::hasColumn('surat_ket_wargas', 'tgl_acara_selesai')) {
                $table->date('tgl_acara_selesai')->nullable()->after('tgl_acara_mulai');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('surat_ket_wargas')) {
            return;
        }

        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (Schema::hasColumn('surat_ket_wargas', 'tgl_acara_selesai')) {
                $table->dropColumn('tgl_acara_selesai');
            }
            if (Schema::hasColumn('surat_ket_wargas', 'tgl_acara_mulai')) {
                $table->dropColumn('tgl_acara_mulai');
            }
        });
    }
};
