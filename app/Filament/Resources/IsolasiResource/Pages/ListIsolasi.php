<?php

namespace App\Filament\Resources\IsolasiResource\Pages;

use App\Filament\Concerns\HasPageInfo;
use App\Filament\Resources\IsolasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIsolasi extends ListRecords
{
    use HasPageInfo;

    protected static string $resource = IsolasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->infoAction(
                'Tentang Ruang Isolasi',
                'Halaman ini untuk petugas kesehatan (Menkes) mencatat mahasiswa yang <strong>diisolasi</strong> karena sakit, beserta detail sakitnya. Kapasitas ruang isolasi terbatas (mis. 2). Klik <strong>Selesai</strong> saat mahasiswa keluar dari isolasi. Data ini tampil di kartu Ruang Isolasi pada layar kiosk.'
            ),
            Actions\CreateAction::make()->label('Masukkan ke Isolasi'),
        ];
    }
}
