<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Dashboard';
    }
}
