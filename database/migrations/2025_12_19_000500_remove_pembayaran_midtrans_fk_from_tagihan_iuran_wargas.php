<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas')) {
            return;
        }

        $constraint = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'tagihan_iuran_wargas')
            ->where('COLUMN_NAME', 'PembayaranMidtrans_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) use ($constraint) {
            if ($constraint) {
                $table->dropForeign($constraint);
            }
            if (Schema::hasColumn('tagihan_iuran_wargas', 'PembayaranMidtrans_id')) {
                $table->dropColumn('PembayaranMidtrans_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tagihan_iuran_wargas')) {
            return;
        }

        Schema::table('tagihan_iuran_wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('tagihan_iuran_wargas', 'PembayaranMidtrans_id')) {
                $table->string('PembayaranMidtrans_id', 30)->nullable();
            }
        });
    }
};
