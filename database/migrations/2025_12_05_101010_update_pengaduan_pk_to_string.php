<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengaduan')) {
            return;
        }

        Schema::table('pengaduan', function (Blueprint $table) {
            if (! Schema::hasColumn('pengaduan', 'pengaduan_id')) {
                $table->string('pengaduan_id', 64)->nullable()->after('id');
            }
        });

        $rows = DB::table('pengaduan')->orderBy('tgl_pengajuan')->orderBy('id')->get(['id', 'tgl_pengajuan']);
        $seqByDate = [];
        foreach ($rows as $row) {
            $d = $row->tgl_pengajuan ? \Carbon\Carbon::parse($row->tgl_pengajuan) : now();
            $key = $d->format('Y-m-d');
            $seqByDate[$key] = ($seqByDate[$key] ?? 0) + 1;
            $num = $seqByDate[$key];
            $dateStr = $d->format('dmY');
            $newId = 'ADU-WRG-'.str_pad((string) $num, 3, '0', STR_PAD_LEFT).'-'.$dateStr;
            DB::table('pengaduan')->where('id', $row->id)->update(['pengaduan_id' => $newId]);
        }

        try {
            DB::statement('ALTER TABLE `pengaduan` MODIFY `id` BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE `pengaduan` DROP PRIMARY KEY');
        } catch (\Throwable $e) {
        }

        Schema::table('pengaduan', function (Blueprint $table) {
            if (Schema::hasColumn('pengaduan', 'id')) {
                $table->dropColumn('id');
            }
        });

        Schema::table('pengaduan', function (Blueprint $table) {
            if (Schema::hasColumn('pengaduan', 'pengaduan_id')) {
                $table->primary('pengaduan_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pengaduan')) {
            return;
        }
        Schema::table('pengaduan', function (Blueprint $table) {
            if (! Schema::hasColumn('pengaduan', 'id')) {
                $table->unsignedBigInteger('id')->nullable()->first();
            }
        });
    }
};
