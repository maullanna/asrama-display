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
}
