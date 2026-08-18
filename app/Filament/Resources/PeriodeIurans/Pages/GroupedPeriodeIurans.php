<?php

namespace App\Filament\Resources\PeriodeIurans\Pages;

use App\Filament\Resources\PeriodeIurans\PeriodeIuranResource;
use App\Models\PeriodeIuran;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class GroupedPeriodeIurans extends Page
{
    protected static string $resource = PeriodeIuranResource::class;

    protected static string $pluralLabel = 'Periode Iuran';

    protected string $view = 'filament.resources.periode-iurans.pages.grouped-periode-iurans';

    public function getHeading(): string
    {
        return 'Periode Iuran';
    }

    public function getTitle(): string
    {
        return 'Periode Iuran';
    }

    public function getBreadcrumb(): string
    {
        return 'Periode Iuran';
    }

    public function getBreadcrumbs(): array
    {
        return [
            PeriodeIuranResource::getUrl('index') => 'Periode Iuran',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('generate_periode_iuran')
                ->label('Generate Periode Iuran')
                ->form([
                    TextInput::make('tahun')
                        ->label('Tahun')
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(2100)
                        ->required(),
                    Select::make('bulan_mulai')
                        ->label('Bulan Mulai')
                        ->options([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ])
                        ->required(),
                    Select::make('bulan_selesai')
                        ->label('Bulan Selesai')
                        ->options([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ])
                        ->required(),
                    TextInput::make('tanggal_jatuh_tempo')
                        ->label('Tanggal Jatuh Tempo')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(31)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    DB::beginTransaction();
                    try {
                        $tahun = (int) ($data['tahun'] ?? now()->year);
                        $startMonth = (int) ($data['bulan_mulai'] ?? 1);
                        $endMonth = (int) ($data['bulan_selesai'] ?? 12);
                        $tanggal = (int) ($data['tanggal_jatuh_tempo'] ?? 1);

                        if ($startMonth > $endMonth) {
                            [$startMonth, $endMonth] = [$endMonth, $startMonth];
                        }

                        $bulanList = range($startMonth, $endMonth);
                        $created = 0;

                        foreach ($bulanList as $bulan) {
                            $bulanInt = (int) $bulan;

                            $exists = PeriodeIuran::query()
                                ->where('tahun', $tahun)
                                ->where('bulan', $bulanInt)
                                ->exists();
                            if ($exists) {
                                continue;
                            }

                            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulanInt, $tahun);
                            $safeDay = max(1, min($tanggal, $daysInMonth));
                            $tanggalJatuhTempo = sprintf('%04d-%02d-%02d', $tahun, $bulanInt, $safeDay);

                            PeriodeIuran::create([
                                'tahun' => $tahun,
                                'bulan' => $bulanInt,
                                'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                            ]);
                            $created++;
                        }

                        DB::commit();

                        if ($created > 0) {
                            Notification::make()
                                ->title('Generate periode iuran berhasil')
                                ->body('Jumlah periode dibuat: '.$created)
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Tidak ada periode baru yang dibuat')
                                ->body('Semua periode yang dipilih sudah tersedia')
                                ->warning()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        Notification::make()
                            ->title('Gagal generate periode iuran')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function deletePeriode(string $id): void
    {
        try {
            $model = PeriodeIuran::query()
                ->where('periode_iuran_id', $id)
                ->first();
            if ($model) {
                $model->delete();
                Notification::make()
                    ->title('Periode iuran dihapus')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Periode tidak ditemukan')
                    ->warning()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal menghapus periode iuran')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getViewData(): array
    {
        $map = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $periodes = PeriodeIuran::query()
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan')
            ->get();

        $years = [];
        foreach ($periodes as $p) {
            $tahun = (int) $p->tahun;
            if (! isset($years[$tahun])) {
                $years[$tahun] = [
                    'year' => $tahun,
                    'rows' => [],
                ];
            }
            $years[$tahun]['rows'][] = [
                'id' => $p->periode_iuran_id,
                'bulan' => $map[(int) $p->bulan] ?? (string) $p->bulan,
                'jatuh_tempo' => $p->tanggal_jatuh_tempo,
            ];
        }

        return [
            'years' => array_values($years),
        ];
    }
}
