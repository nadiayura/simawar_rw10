<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ketua_rts')) {
            return;
        }

        // Tambah kolom ketua_rt_id
        Schema::table('ketua_rts', function (Blueprint $table) {
            if (! Schema::hasColumn('ketua_rts', 'ketua_rt_id')) {
                $table->string('ketua_rt_id', 64)->nullable()->after('id');
            }
        });

        // Backfill nilai ketua_rt_id dengan format K-{no_rt_id}-{seq}
        $rows = DB::table('ketua_rts')->select('id', 'no_rt_id')->orderBy('id')->get();
        $seqByRt = [];
        foreach ($rows as $r) {
            $rt = (string) ($r->no_rt_id ?? 'RT-000');
            $seqByRt[$rt] = ($seqByRt[$rt] ?? 0) + 1;
            $seq = str_pad((string) $seqByRt[$rt], 3, '0', STR_PAD_LEFT);
            $newId = 'K-'.$rt.'-'.$seq;
            DB::table('ketua_rts')->where('id', $r->id)->update(['ketua_rt_id' => $newId]);
        }

        // Ubah kolom id agar bukan auto_increment, lalu buang primary key
        DB::statement('ALTER TABLE ketua_rts MODIFY COLUMN id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE ketua_rts DROP PRIMARY KEY');
        DB::statement('ALTER TABLE ketua_rts ADD PRIMARY KEY (ketua_rt_id)');

        // Hapus kolom id
        Schema::table('ketua_rts', function (Blueprint $table) {
            if (Schema::hasColumn('ketua_rts', 'id')) {
                $table->dropColumn('id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ketua_rts')) {
            return;
        }

        // Tambahkan kembali kolom id auto-increment
        Schema::table('ketua_rts', function (Blueprint $table) {
            if (! Schema::hasColumn('ketua_rts', 'id')) {
                $table->bigIncrements('id')->first();
            }
        });

        // Drop PK ketua_rt_id dan kembalikan ke id
        DB::statement('ALTER TABLE ketua_rts DROP PRIMARY KEY');
        DB::statement('ALTER TABLE ketua_rts ADD PRIMARY KEY (id)');

        // Opsional: hapus kolom ketua_rt_id
        Schema::table('ketua_rts', function (Blueprint $table) {
            if (Schema::hasColumn('ketua_rts', 'ketua_rt_id')) {
                $table->dropColumn('ketua_rt_id');
            }
        });
    }
};
