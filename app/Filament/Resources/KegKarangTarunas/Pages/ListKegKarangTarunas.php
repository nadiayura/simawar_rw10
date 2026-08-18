<?php

namespace App\Filament\Resources\KegKarangTarunas\Pages;

use App\Filament\Resources\KegKarangTarunas\KegKarangTarunaResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListKegKarangTarunas extends ListRecords
{
    protected static string $resource = KegKarangTarunaResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $user = auth()->user();
        $rtId = optional($user?->warga)->no_rt_id;

        if (! $rtId) {
            return $query;
        }

        return $query->whereHas('pjWarga', function (Builder $wargaQuery) use ($rtId) {
            $wargaQuery->where('no_rt_id', $rtId);
        });
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
