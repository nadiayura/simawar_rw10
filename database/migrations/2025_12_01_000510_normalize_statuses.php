<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add status_id columns
        Schema::table('pengaduan', function (Blueprint $table) {
            if (! Schema::hasColumn('pengaduan', 'status_id')) {
                $table->unsignedBigInteger('status_id')->nullable()->after('jdl_pengaduan');
            }
        });
        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_ket_wargas', 'status_id')) {
                $table->unsignedBigInteger('status_id')->nullable()->after('dok_pendukung');
            }
        });
        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('tagihan_iuran_wargas', 'status_id')) {
                $table->unsignedBigInteger('status_id')->nullable()->after('nominal_tagihan');
            }
        });

        // Backfill with mapping to statuses.keterangan
        DB::statement('UPDATE pengaduan p JOIN statuses s ON LOWER(p.status) = LOWER(s.keterangan) SET p.status_id = s.id');
        DB::statement('UPDATE surat_ket_wargas skw JOIN statuses s ON LOWER(skw.status) = LOWER(s.keterangan) SET skw.status_id = s.id');
        DB::statement('UPDATE tagihan_iuran_wargas tiw JOIN statuses s ON LOWER(tiw.status_tagihan) = LOWER(s.keterangan) SET tiw.status_id = s.id');

        // Drop old columns and add foreign keys
        Schema::table('pengaduan', function (Blueprint $table) {
            if (Schema::hasColumn('pengaduan', 'status')) {
                $table->dropColumn('status');
            }
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('restrict');
        });
        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            if (Schema::hasColumn('surat_ket_wargas', 'status')) {
                $table->dropColumn('status');
            }
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('restrict');
        });
        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            if (Schema::hasColumn('tagihan_iuran_wargas', 'status_tagihan')) {
                $table->dropColumn('status_tagihan');
            }
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        // Restore old columns
        Schema::table('pengaduan', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->enum('status', ['pending', 'diproses', 'selesai', 'ditolak'])->default('pending');
            $table->dropColumn('status_id');
        });
        Schema::table('surat_ket_wargas', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->string('status')->nullable();
            $table->dropColumn('status_id');
        });
        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->enum('status_tagihan', ['belum_bayar', 'menunggu_pembayaran', 'lunas', 'kedaluwarsa'])->default('belum_bayar');
            $table->dropColumn('status_id');
        });
    }
};
