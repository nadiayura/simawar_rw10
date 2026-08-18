<?php

namespace App\Filament\Warga\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Register;

class WargaRegister extends Register
{
    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('login')
            ->url(filament()->getLoginUrl());
    }
}
