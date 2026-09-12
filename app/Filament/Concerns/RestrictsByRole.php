<?php

namespace App\Filament\Concerns;

trait RestrictsByRole
{
    /**
     * Daftar role yang boleh melihat & mengakses resource/halaman ini.
     * Kosong = semua role boleh.
     *
     * @return array<int, string>
     */
    protected static function allowedRoles(): array
    {
        return [];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::roleAllowed();
    }

    public static function canViewAny(): bool
    {
        return static::roleAllowed();
    }

    protected static function roleAllowed(): bool
    {
        $roles = static::allowedRoles();

        if ($roles === []) {
            return true;
        }

        return in_array(auth()->user()?->role, $roles, true);
    }
}
