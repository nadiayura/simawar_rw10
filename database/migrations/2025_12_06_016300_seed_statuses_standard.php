<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function nextSeq(): string
    {
        $max = DB::table('statuses')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(status_id, '-', -1) AS UNSIGNED)) AS m")
            ->whereRaw("status_id LIKE 'STS-%'")
            ->value('m');
        $n = ((int) ($max ?? 0)) + 1;

        return 'STS-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT);
    }

    public function up(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }

        $insertIfMissing = function (string $fitur, array $names) {
            foreach ($names as $name) {
                $exists = DB::table('statuses')
                    ->where('fitur', $fitur)
                    ->whereRaw('LOWER(keterangan) = ?', [strtolower($name)])
                    ->exists();
                if (! $exists) {
                    DB::table('statuses')->insert([
                        'status_id' => $this->nextSeq(),
                        'keterangan' => $name,
                        'fitur' => $fitur,
                    ]);
                }
            }
        };

        // 1) Keuangan: tambahkan status verifikasi
        $insertIfMissing('keuangan', ['Menunggu Verifikasi']);

        // A) Surat
        $insertIfMissing('surat', ['Diajukan', 'Menunggu Verifikasi', 'Diproses', 'Selesai', 'Ditolak']);

        // B) Pengaduan
        $insertIfMissing('pengaduan', ['Pending', 'Diverifikasi', 'Diproses', 'Selesai', 'Ditolak']);

        // C) Keg Warga
        $insertIfMissing('keg_warga', ['Dijadwalkan', 'Berlangsung', 'Selesai']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }
        DB::table('statuses')->whereIn('fitur', ['keuangan', 'surat', 'pengaduan', 'keg_warga'])
            ->whereIn('keterangan', [
                'Menunggu Verifikasi',
                'Diajukan', 'Diproses', 'Selesai', 'Ditolak',
                'Pending', 'Diverifikasi',
                'Dijadwalkan', 'Berlangsung',
            ])->delete();
    }
};
