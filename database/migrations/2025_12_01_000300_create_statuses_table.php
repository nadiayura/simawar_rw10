<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('keterangan', 64)->unique();
        });

        DB::table('statuses')->insert(array_map(fn ($k) => ['keterangan' => $k], [
            'Pending', 'Diproses', 'Selesai', 'Ditolak',
            'Diajukan', 'Menunggu Verifikasi',
            'Belum bayar', 'Menunggu pembayaran', 'Lunas', 'Kedaluwarsa',
        ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
