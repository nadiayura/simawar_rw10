<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'lg' => 3,
            'xl' => 3,
        ];
    }
}
