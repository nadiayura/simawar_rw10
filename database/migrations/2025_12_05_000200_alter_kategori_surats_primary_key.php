<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kategori_surats')) {
            return;
        }

        if (! Schema::hasColumn('kategori_surats', 'kategori_surat_id')) {
            Schema::table('kategori_surats', function (Blueprint $table) {
                $table->string('kategori_surat_id', 32)->nullable()->after('id');
            });
        }
        if (! Schema::hasColumn('kategori_surats', 'old_id')) {
            Schema::table('kategori_surats', function (Blueprint $table) {
                $table->unsignedBigInteger('old_id')->nullable()->after('kategori_surat_id');
            });
        }

        $rows = DB::table('kategori_surats')->orderBy('id')->get(['id']);
        $seq = 0;
        foreach ($rows as $row) {
            $seq++;
            $newId = 'KTGR-SRT_KET-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            DB::table('kategori_surats')->where('id', $row->id)->update(['kategori_surat_id' => $newId, 'old_id' => $row->id]);
        }

        // Drop foreign keys referencing kategori_surats.id before altering
        $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('REFERENCED_TABLE_NAME', 'kategori_surats')
            ->where('REFERENCED_COLUMN_NAME', 'id')
            ->get(['TABLE_NAME', 'CONSTRAINT_NAME']);

        foreach ($refs as $ref) {
            Schema::table($ref->TABLE_NAME, function (Blueprint $table) use ($ref) {
                $table->dropForeign($ref->CONSTRAINT_NAME);
            });
        }

        // Remove AUTO_INCREMENT before dropping primary key
        DB::statement('ALTER TABLE kategori_surats MODIFY id BIGINT UNSIGNED NOT NULL');
        // Remove old PK and column, set new PK
        DB::statement('ALTER TABLE kategori_surats DROP PRIMARY KEY');
        Schema::table('kategori_surats', function (Blueprint $table) {
            $table->dropColumn('id');
        });
        DB::statement('ALTER TABLE kategori_surats MODIFY kategori_surat_id VARCHAR(32) NOT NULL');
        Schema::table('kategori_surats', function (Blueprint $table) {
            $table->primary('kategori_surat_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('kategori_surats')) {
            return;
        }
        Schema::table('kategori_surats', function (Blueprint $table) {
            $table->dropPrimary();
            $table->bigIncrements('id')->first();
        });
        // Keep kategori_surat_id for data safety
    }
};
