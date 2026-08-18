<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_pengaduans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (Schema::hasTable('pengaduan') && Schema::hasColumn('pengaduan', 'jenis_pengaduan')) {
            $rows = DB::table('pengaduan')->select('jenis_pengaduan')->distinct()->get();
            foreach ($rows as $row) {
                $name = trim((string) $row->jenis_pengaduan);
                if ($name !== '') {
                    DB::table('jenis_pengaduans')->updateOrInsert(['nama' => $name], ['nama' => $name, 'is_active' => true]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_pengaduans');
    }
};
