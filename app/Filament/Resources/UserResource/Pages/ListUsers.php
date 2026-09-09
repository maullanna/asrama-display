<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Concerns\HasPageInfo;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    use HasPageInfo;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->infoAction(
                'Tentang Halaman Pengguna',
                'Halaman ini untuk mengelola <strong>akun admin panel</strong>. <strong>Super Admin</strong> punya akses penuh termasuk kelola pengguna; <strong>Kordinator</strong> bisa kelola data (mahasiswa, kamar, izin/sakit) tapi tidak bisa membuka menu ini.'
            ),
            Actions\CreateAction::make()->label('Tambah Pengguna'),
        ];
    }
}
