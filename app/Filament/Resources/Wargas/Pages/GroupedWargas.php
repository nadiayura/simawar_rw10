<?php

namespace App\Filament\Resources\Wargas\Pages;

use App\Filament\Resources\Wargas\WargaResource;
use App\Models\Iuran;
use App\Models\NoRt;
use App\Models\Warga;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class GroupedWargas extends Page
{
    protected static string $resource = WargaResource::class;

    protected static string $pluralLabel = 'Data Warga';

    protected string $view = 'filament.resources.wargas.pages.grouped-wargas';

    public function getHeading(): string
    {
        return 'Data Warga';
    }

    public function getTitle(): string
    {
        return 'Data Warga';
    }

    public function getBreadcrumb(): string
    {
        return 'Data Warga';
    }

    public function getBreadcrumbs(): array
    {
        return [
            WargaResource::getUrl('index') => 'Data Warga',
        ];
    }

    protected function getTabs(): array
    {
        return [
            'index' => \Filament\Schemas\Components\Tabs\Tab::make('Data Warga')
                ->url(WargaResource::getUrl('index'))
                ->icon('heroicon-o-users'),
            'verifikasi' => \Filament\Schemas\Components\Tabs\Tab::make('Verifikasi Warga Baru')
                ->url(WargaResource::getUrl('verifikasi'))
                ->icon('heroicon-o-check-circle'),
        ];
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        $pendingQuery = Warga::query()
            ->whereHas('user', function ($q) {
                $q->whereHas('role', function ($rq) {
                    $rq->where('name', 'tamu');
                });
            })
            ->orderBy('nama');

        if ($user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id) {
            $pendingQuery->where('no_rt_id', $user->warga->no_rt_id);
        }

        $pendingRows = $pendingQuery->get()->map(function (Warga $w) {
            return [
                'nik' => $w->warga_nik,
                'nama' => $w->nama,
                'status' => $w->status_tinggal,
                'rt' => $w->no_rt_id,
                'no_hp' => $w->no_hp,
                'iuran' => optional(Iuran::find($w->iuran_id))->nama_iuran,
            ];
        })->toArray();

        $rtQuery = NoRt::query()->orderBy('nomor');
        if ($user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id) {
            $rtQuery->where('no_rt_id', $user->warga->no_rt_id);
        }

        $rts = $rtQuery->get();

        $groups = [];
        foreach ($rts as $rt) {
            $rows = Warga::query()
                ->where('no_rt_id', $rt->no_rt_id)
                ->whereDoesntHave('user', function ($q) {
                    $q->whereHas('role', function ($rq) {
                        $rq->where('name', 'tamu');
                    });
                })
                ->orderBy('nama')
                ->get()
                ->map(function (Warga $w) {
                    return [
                        'nik' => $w->warga_nik,
                        'nama' => $w->nama,
                        'status' => $w->status_tinggal,
                        'rt' => $w->no_rt_id,
                        'no_hp' => $w->no_hp,
                        'iuran' => optional(Iuran::find($w->iuran_id))->nama_iuran,
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
            'pending' => $pendingRows,
            'groups' => $groups,
        ];
    }

    public function deleteWarga(string $nik): void
    {
        $record = Warga::query()->findOrFail($nik);

        if (! WargaResource::canDelete($record)) {
            abort(403);
        }

        $record->delete();

        Notification::make()
            ->title('Data Warga berhasil dihapus')
            ->success()
            ->send();
    }
}
