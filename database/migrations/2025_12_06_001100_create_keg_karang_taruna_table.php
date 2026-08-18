<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('keg_karang_taruna')) {
            Schema::create('keg_karang_taruna', function (Blueprint $table) {
                $table->string('keg_karang_taruna_id', 64)->primary();
                $table->string('nama_kegiatan', 150);
                $table->text('deskripsi')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('penanggung_jawab', 32)->nullable();
                $table->string('status_kegiatan', 32)->default('Terjadwal');
                $table->json('dokumentasi')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('keg_karang_taruna', function (Blueprint $table) {
                if (! Schema::hasColumn('keg_karang_taruna', 'keg_karang_taruna_id')) {
                    $table->string('keg_karang_taruna_id', 64)->nullable();
                }
                if (! Schema::hasColumn('keg_karang_taruna', 'nama_kegiatan')) {
                    $table->string('nama_kegiatan', 150)->nullable();
                }
                if (! Schema::hasColumn('keg_karang_taruna', 'deskripsi')) {
                    $table->text('deskripsi')->nullable();
                }
                if (! Schema::hasColumn('keg_karang_taruna', 'tanggal')) {
                    $table->date('tanggal')->nullable();
                }
                if (! Schema::hasColumn('keg_karang_taruna', 'penanggung_jawab')) {
                    $table->string('penanggung_jawab', 32)->nullable();
                }
                if (! Schema::hasColumn('keg_karang_taruna', 'status_kegiatan')) {
                    $table->string('status_kegiatan', 32)->default('Terjadwal');
                }
                if (! Schema::hasColumn('keg_karang_taruna', 'dokumentasi')) {
                    $table->json('dokumentasi')->nullable();
                }
                if (! Schema::hasColumn('keg_karang_taruna', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('keg_karang_taruna');
    }
};
