<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pembayaran_midtrans')) {
            return;
        }

        if (! Schema::hasColumn('pembayaran_midtrans', 'PembayaranMidtrans_id')) {
            Schema::table('pembayaran_midtrans', function (Blueprint $table) {
                $table->string('PembayaranMidtrans_id', 30)->nullable()->after('id');
            });
        }

        $rows = DB::table('pembayaran_midtrans')
            ->orderBy('created_at')
            ->get(['id', 'created_at']);

        $counters = [];
        foreach ($rows as $row) {
            $created = $row->created_at ? \Carbon\Carbon::parse($row->created_at) : now();
            $key = $created->format('d-m-Y');
            $counters[$key] = ($counters[$key] ?? 0) + 1;
            $seq = str_pad((string) $counters[$key], 3, '0', STR_PAD_LEFT);
            $newId = 'BYR-MDRS-'.$key.'-'.$seq;
            DB::table('pembayaran_midtrans')
                ->where('id', $row->id)
                ->update(['PembayaranMidtrans_id' => $newId]);
        }

        // Drop any foreign keys referencing pembayaran_midtrans.id before altering
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('REFERENCED_TABLE_NAME', 'pembayaran_midtrans')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->get(['TABLE_NAME', 'CONSTRAINT_NAME']);

        foreach ($refs as $ref) {
            Schema::table($ref->TABLE_NAME, function (Blueprint $table) use ($ref) {
                $table->dropForeign($ref->CONSTRAINT_NAME);
            });
        }

        DB::statement('ALTER TABLE pembayaran_midtrans MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE pembayaran_midtrans DROP PRIMARY KEY');

        if (Schema::hasColumn('pembayaran_midtrans', 'id')) {
            Schema::table('pembayaran_midtrans', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }

        DB::statement('ALTER TABLE pembayaran_midtrans MODIFY PembayaranMidtrans_id VARCHAR(30) NOT NULL');
        Schema::table('pembayaran_midtrans', function (Blueprint $table) {
            $table->primary('PembayaranMidtrans_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pembayaran_midtrans')) {
            return;
        }

        // Kembalikan ke kolom id bigIncrements
        Schema::table('pembayaran_midtrans', function (Blueprint $table) {
            $table->dropPrimary();
            $table->bigIncrements('id')->first();
        });

        // Tidak menghapus kolom string agar tidak hilang data
    }
};
