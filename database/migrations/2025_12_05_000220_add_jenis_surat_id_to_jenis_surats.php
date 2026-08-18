<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_surats')) {
            return;
        }
        if (! Schema::hasColumn('jenis_surats', 'jenis_surat_id')) {
            Schema::table('jenis_surats', function (Blueprint $table) {
                $table->string('jenis_surat_id', 32)->nullable()->after('id');
            });
        }

        $rows = DB::table('jenis_surats')->orderBy('id')->get(['id']);
        $seq = 0;
        foreach ($rows as $row) {
            $seq++;
            $newId = 'JNS-SRT-KET-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            DB::table('jenis_surats')->where('id', $row->id)->update(['jenis_surat_id' => $newId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('jenis_surats')) {
            return;
        }
        if (Schema::hasColumn('jenis_surats', 'jenis_surat_id')) {
            Schema::table('jenis_surats', function (Blueprint $table) {
                $table->dropColumn('jenis_surat_id');
            });
        }
    }
};
