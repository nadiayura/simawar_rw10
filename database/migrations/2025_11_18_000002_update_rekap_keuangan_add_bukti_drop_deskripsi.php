<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add bukti column as JSON to support multiple files
        if (! Schema::hasColumn('rekap_keuangan', 'bukti')) {
            Schema::table('rekap_keuangan', function (Blueprint $table) {
                $table->json('bukti')->nullable()->after('referensi_id');
            });
        }

        // Drop deskripsi column if present
        if (Schema::hasColumn('rekap_keuangan', 'deskripsi')) {
            Schema::table('rekap_keuangan', function (Blueprint $table) {
                $table->dropColumn('deskripsi');
            });
        }
    }

    public function down(): void
    {
        // Restore deskripsi and drop bukti
        if (! Schema::hasColumn('rekap_keuangan', 'deskripsi')) {
            Schema::table('rekap_keuangan', function (Blueprint $table) {
                $table->text('deskripsi')->nullable()->after('referensi_id');
            });
        }

        if (Schema::hasColumn('rekap_keuangan', 'bukti')) {
            Schema::table('rekap_keuangan', function (Blueprint $table) {
                $table->dropColumn('bukti');
            });
        }
    }
};
