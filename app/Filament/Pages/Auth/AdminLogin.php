<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login;
use Illuminate\Contracts\Support\Htmlable;

class AdminLogin extends Login
{
    public function getHeading(): string|Htmlable
    {
        return 'Log In';
    }
}
