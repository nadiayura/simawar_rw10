<?php

namespace App\Filament\Resources\Strukturals\Pages;

use App\Filament\Resources\Strukturals\StrukturalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;

class ListStrukturals extends ListRecords
{
    protected static string $resource = StrukturalResource::class;

    public function mount(): void
    {
        // Check authorization before mounting the page
        $user = auth()->user();
        
        if (!$user || !$user->role || (!$user->role->isRW() && !$user->role->isRT())) {
            throw new AuthorizationException('You do not have permission to access this resource.');
        }
        
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => StrukturalResource::canCreate()),
        ];
    }
}
