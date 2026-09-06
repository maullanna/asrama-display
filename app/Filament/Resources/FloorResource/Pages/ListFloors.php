<?php

namespace App\Filament\Resources\FloorResource\Pages;

use App\Filament\Concerns\HasPageInfo;
use App\Filament\Resources\FloorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFloors extends ListRecords
{
    use HasPageInfo;

    protected static string $resource = FloorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->infoAction(
                'Tentang Halaman Lantai',
                'Halaman ini untuk mengelola daftar <strong>lantai</strong> asrama (contoh: Lantai 1, Lantai 2). Setiap lantai bisa memiliki banyak kamar, dan urutannya menentukan susunan tampilan di layar kiosk (TV).'
            ),
            Actions\CreateAction::make(),
        ];
    }
}
