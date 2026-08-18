<?php

namespace Database\Seeders;

use App\Models\Iuran;
use App\Models\NoRt;
use App\Models\Warga;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('WARGA-RW-010.xlsx');
        if (! file_exists($filePath)) {
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $headerRowIndex = null;
        foreach ($rows as $idx => $row) {
            foreach ($row as $cell) {
                if (strtolower(trim((string) $cell)) === 'warga_nik') {
                    $headerRowIndex = $idx;
                    break 2;
                }
            }
        }
        if (! $headerRowIndex) {
            return;
        }

        $headerRow = $rows[$headerRowIndex] ?? [];
        $colByHeader = [];
        foreach ($headerRow as $col => $header) {
            $h = strtolower(trim((string) $header));
            if ($h !== '') {
                $colByHeader[$h] = $col;
            }
        }

        $iuranIds = [];
        for ($i = $headerRowIndex + 1; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? null;
            if (! is_array($row)) {
                continue;
            }
            $rawNikCell = (string) ($row[$colByHeader['warga_nik'] ?? ''] ?? '');
            $rawNik = preg_replace('/\D/', '', $rawNikCell);
            if ($rawNik === '') {
                continue;
            }
            $iuranId = trim((string) ($row[$colByHeader['iuran_id'] ?? ''] ?? ''));
            if ($iuranId !== '') {
                $iuranIds[$iuranId] = true;
            }
        }

        foreach (array_keys($iuranIds) as $iuranId) {
            $exists = Iuran::query()->where('iuran_id', $iuranId)->exists();
            if (! $exists) {
                $iuran = new Iuran([
                    'nama_iuran' => $iuranId,
                    'jumlah_default' => 0,
                    'deskripsi' => null,
                ]);
                $iuran->iuran_id = $iuranId;
                $iuran->save();

                continue;
            }

            Iuran::query()->where('iuran_id', $iuranId)->update([
                'nama_iuran' => $iuranId,
                'jumlah_default' => 0,
                'deskripsi' => null,
            ]);
        }

        for ($i = $headerRowIndex + 1; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? null;
            if (! is_array($row)) {
                continue;
            }

            $rawNikCell = (string) ($row[$colByHeader['warga_nik'] ?? ''] ?? '');
            $rawNik = preg_replace('/\D/', '', $rawNikCell);
            if ($rawNik === '') {
                continue;
            }
            $wargaNik = 'WRG-'.$rawNik;

            $rtCell = trim((string) ($row[$colByHeader['no_rt_id'] ?? ''] ?? ''));
            $rtNomor = preg_replace('/\D/', '', $rtCell);
            $rtNomor = $rtNomor !== '' ? str_pad($rtNomor, 2, '0', STR_PAD_LEFT) : null;
            $noRtId = null;
            if ($rtNomor) {
                $noRtId = NoRt::query()->where('nomor', $rtNomor)->value('no_rt_id');
            }

            $iuranId = trim((string) ($row[$colByHeader['iuran_id'] ?? ''] ?? '')) ?: null;

            $rawPhone = trim((string) ($row[$colByHeader['no_hp'] ?? ''] ?? ''));
            $phone = preg_replace('/\D/', '', $rawPhone);
            if (is_string($phone) && strlen($phone) > 15) {
                $phone = substr($phone, 0, 15);
            }

            $data = [
                'warga_nik' => $wargaNik,
                'email' => trim((string) ($row[$colByHeader['email'] ?? ''] ?? '')) ?: null,
                'nama' => trim((string) ($row[$colByHeader['nama'] ?? ''] ?? '')),
                'iuran_id' => $iuranId,
                'jenis_kelamin' => trim((string) ($row[$colByHeader['jenis_kelamin'] ?? ''] ?? '')),
                'agama' => trim((string) ($row[$colByHeader['agama'] ?? ''] ?? '')),
                'status_tinggal' => trim((string) ($row[$colByHeader['status_tinggal'] ?? ''] ?? '')),
                'alamat' => trim((string) ($row[$colByHeader['alamat'] ?? ''] ?? '')),
                'no_rt_id' => $noRtId,
                'no_hp' => $phone !== '' ? $phone : null,
            ];

            Warga::updateOrCreate(
                ['warga_nik' => $wargaNik],
                $data
            );
        }
    }
}
