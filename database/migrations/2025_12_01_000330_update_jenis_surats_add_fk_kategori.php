<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table) {
            if (! Schema::hasColumn('jenis_surats', 'kategori_id')) {
                $table->unsignedBigInteger('kategori_id')->nullable()->after('id');
            }
        });

        // Backfill kategori_id by matching kategori string to kategori_surats.nama
        if (Schema::hasColumn('jenis_surats', 'kategori')) {
            DB::statement('UPDATE jenis_surats js JOIN kategori_surats ks ON js.kategori = ks.nama SET js.kategori_id = ks.id');
        }

        Schema::table('jenis_surats', function (Blueprint $table) {
            if (Schema::hasColumn('jenis_surats', 'kategori')) {
                $table->dropColumn('kategori');
            }
            $table->foreign('kategori_id')->references('id')->on('kategori_surats')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->string('kategori', 64)->nullable();
            $table->dropColumn('kategori_id');
        });
    }
};
