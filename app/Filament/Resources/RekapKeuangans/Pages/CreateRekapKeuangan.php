<?php

namespace App\Filament\Resources\RekapKeuangans\Pages;

use App\Filament\Resources\RekapKeuangans\RekapKeuanganResource;
use App\Models\PembayaranMidtrans;
use App\Models\Status;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Schema as DbSchema;

class CreateRekapKeuangan extends CreateRecord
{
    protected static string $resource = RekapKeuanganResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['jenis'] ?? null) === 'masuk') {
            if (empty($data['PembayaranMidtrans_id'])) {
                if (DbSchema::hasColumn('rekap_keuangan', 'PembayaranMidtrans_id')) {
                    $latestSettlementId = PembayaranMidtrans::query()
                        ->where('status_id', Status::idForFitur('keuangan', 'settlement'))
                        ->latest('updated_at')
                        ->value('PembayaranMidtrans_id');
                    if ($latestSettlementId) {
                        $data['PembayaranMidtrans_id'] = $latestSettlementId;
                    }
                }
            }
        } else {
            if (DbSchema::hasColumn('rekap_keuangan', 'PembayaranMidtrans_id')) {
                $data['PembayaranMidtrans_id'] = null;
            } else {
                unset($data['PembayaranMidtrans_id']);
            }
        }

        return $data;
    }
}
