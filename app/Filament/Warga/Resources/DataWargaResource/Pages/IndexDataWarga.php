<?php

namespace App\Filament\Warga\Resources\DataWargaResource\Pages;

use App\Filament\Warga\Resources\DataWargaResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class IndexDataWarga extends Page
{
    protected static string $resource = DataWargaResource::class;

    protected string $view = 'filament::pages.blank';

    public function mount(): void
    {
        $user = Auth::user();
        if ($user && $user->warga_nik) {
            $this->redirect(DataWargaResource::getUrl('view', ['record' => $user->warga_nik]));

            return;
        }

        $this->redirect(DataWargaResource::getUrl('create'));
    }
}
