<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Concerns\HasPageInfo;
use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    use HasPageInfo;

    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->infoAction(
                'Tentang Halaman Mahasiswa',
                'Halaman ini berisi data <strong>mahasiswa</strong> penghuni asrama: nama, NIM, foto, kamar yang ditempati, PIN mesin fingerprint, penanda ketua kamar, dan status aktif. Data di sinilah yang tampil di layar kiosk (TV).'
            ),
            Actions\CreateAction::make(),
        ];
    }
}
