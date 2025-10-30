<?php

namespace App\Filament\Resources\KetuaRts\Pages;

use App\Filament\Resources\KetuaRts\KetuaRtResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Access\AuthorizationException;

class CreateKetuaRt extends CreateRecord
{
    protected static string $resource = KetuaRtResource::class;

    public function mount(): void
    {
        // Check authorization before mounting the page
        $user = auth()->user();
        
        if (!$user || !$user->role || $user->role->isRT()) {
            throw new AuthorizationException('You do not have permission to create this resource.');
        }
        
        parent::mount();
    }
}
