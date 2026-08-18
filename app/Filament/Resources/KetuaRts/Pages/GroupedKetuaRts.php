<?php

namespace App\Filament\Resources\KetuaRts\Pages;

use App\Filament\Resources\KetuaRts\KetuaRtResource;
use App\Models\KetuaRt;
use App\Models\NoRt;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class GroupedKetuaRts extends Page
{
    protected static string $resource = KetuaRtResource::class;

    protected static string $pluralLabel = 'Struktural RT';

    protected string $view = 'filament.resources.ketuarts.pages.grouped-ketuarts';

    public function getHeading(): string
    {
        return 'Struktural RT';
    }

    public function getTitle(): string
    {
        return 'Struktural RT';
    }

    public function getBreadcrumb(): string
    {
        return 'Struktural RT';
    }

    public function getBreadcrumbs(): array
    {
        return [
            KetuaRtResource::getUrl('index') => 'Struktural RT',
        ];
    }

    protected function getViewData(): array
    {
        $user = request()->user();

        $rtQuery = NoRt::query()->orderBy('nomor');

        if ($user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id) {
            $rtQuery->where('no_rt_id', $user->warga->no_rt_id);
        }

        $rts = $rtQuery->get();

        $groups = [];
        foreach ($rts as $rt) {
            $rows = KetuaRt::query()
                ->with('warga')
                ->where('no_rt_id', $rt->no_rt_id)
                ->orderBy('jabatan')
                ->get()
                ->map(function (KetuaRt $k) {
                    return [
                        'id' => $k->ketua_rt_id,
                        'rt' => $k->no_rt_id,
                        'jabatan' => $k->jabatan,
                        'nama' => $k->warga?->nama,
                        'alamat' => $k->alamat,
                        'no_hp' => $k->no_hp,
                        'periode' => ($k->periode_mulai ? (string) $k->periode_mulai : '').' - '.($k->periode_selesai ? (string) $k->periode_selesai : ''),
                        'is_active' => (bool) $k->is_active,
                    ];
                })
                ->toArray();

            $groups[] = [
                'rt_label' => 'RT - '.str_pad((string) $rt->nomor, 3, '0', STR_PAD_LEFT),
                'rt_id' => $rt->no_rt_id,
                'rows' => $rows,
            ];
        }

        return [
            'groups' => $groups,
        ];
    }

    public function deleteStruktural(string $id): void
    {
        $record = KetuaRt::query()->findOrFail($id);

        if (! KetuaRtResource::canDelete($record)) {
            abort(403);
        }

        $record->delete();

        Notification::make()
            ->title('Struktural RT berhasil dihapus')
            ->success()
            ->send();
    }
}
