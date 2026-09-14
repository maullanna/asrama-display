<?php

namespace App\Filament\Resources\VocationResource\Pages;

use App\Filament\Concerns\HasPageInfo;
use App\Filament\Resources\VocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVocations extends ListRecords
{
    use HasPageInfo;

    protected static string $resource = VocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->infoAction(
                'Tentang Halaman Vokasi',
                'Halaman ini untuk data <strong>mahasiswa vokasi</strong> (kelompok A10) yang sedang pelatihan di luar asrama. Cukup isi nama, foto, dan lokasi (Sunter / Karawang). Data ini tampil di slide VOKASI pada layar kiosk, dikelompokkan per lokasi.'
            ),
            Actions\CreateAction::make()->label('Tambah Mahasiswa Vokasi'),
        ];
    }
}
