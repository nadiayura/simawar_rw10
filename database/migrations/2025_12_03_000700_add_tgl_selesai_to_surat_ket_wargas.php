<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_ket_wargas', 'tgl_selesai')) {
                $table->date('tgl_selesai')->nullable()->after('tgl_pengajuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (Schema::hasColumn('surat_ket_wargas', 'tgl_selesai')) {
                $table->dropColumn('tgl_selesai');
            }
        });
    }
};
