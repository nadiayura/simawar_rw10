<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_surats', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 64)->unique();
        });

        // Seed from existing jenis_surats.kategori values if column exists
        if (Schema::hasTable('jenis_surats') && Schema::hasColumn('jenis_surats', 'kategori')) {
            $rows = DB::table('jenis_surats')->select('kategori')->distinct()->get();
            foreach ($rows as $row) {
                $name = trim((string) $row->kategori);
                if ($name !== '') {
                    DB::table('kategori_surats')->updateOrInsert(['nama' => $name], ['nama' => $name]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_surats');
    }
};
