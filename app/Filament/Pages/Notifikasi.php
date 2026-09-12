<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Notifikasi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'Notifikasi HP';

    protected static ?string $title = 'Notifikasi HP';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.notifikasi';

    // Menkes tidak perlu notifikasi abnormality (itu urusan super admin & koordinator).
    protected static function forRoles(): bool
    {
        return in_array(auth()->user()?->role, ['super_admin', 'koordinator'], true);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::forRoles();
    }

    public static function canAccess(): bool
    {
        return static::forRoles();
    }
}
