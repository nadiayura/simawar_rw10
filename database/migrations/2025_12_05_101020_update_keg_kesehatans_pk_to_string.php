<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('keg_kesehatans')) {
            return;
        }

        Schema::table('keg_kesehatans', function (Blueprint $table) {
            if (! Schema::hasColumn('keg_kesehatans', 'keg_kesehatan_id')) {
                $table->string('keg_kesehatan_id', 64)->nullable()->after('id');
            }
        });

        $rows = DB::table('keg_kesehatans')->orderBy('tgl')->orderBy('id')->get(['id', 'tgl']);
        $seqByDate = [];
        foreach ($rows as $row) {
            $d = $row->tgl ? \Carbon\Carbon::parse($row->tgl) : now();
            $key = $d->format('Y-m-d');
            $seqByDate[$key] = ($seqByDate[$key] ?? 0) + 1;
            $num = $seqByDate[$key];
            $dateStr = $d->format('dmY');
            $newId = 'KSHTN-'.$dateStr.'-'.str_pad((string) $num, 3, '0', STR_PAD_LEFT);
            DB::table('keg_kesehatans')->where('id', $row->id)->update(['keg_kesehatan_id' => $newId]);
        }

        try {
            DB::statement('ALTER TABLE `keg_kesehatans` MODIFY `id` BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE `keg_kesehatans` DROP PRIMARY KEY');
        } catch (\Throwable $e) {
        }

        Schema::table('keg_kesehatans', function (Blueprint $table) {
            if (Schema::hasColumn('keg_kesehatans', 'id')) {
                $table->dropColumn('id');
            }
        });

        Schema::table('keg_kesehatans', function (Blueprint $table) {
            if (Schema::hasColumn('keg_kesehatans', 'keg_kesehatan_id')) {
                $table->primary('keg_kesehatan_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('keg_kesehatans')) {
            return;
        }
        Schema::table('keg_kesehatans', function (Blueprint $table) {
            if (! Schema::hasColumn('keg_kesehatans', 'id')) {
                $table->unsignedBigInteger('id')->nullable()->first();
            }
        });
    }
};
