<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Concerns\HasPageInfo;
use App\Filament\Resources\RoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRooms extends ListRecords
{
    use HasPageInfo;

    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->infoAction(
                'Tentang Halaman Kamar',
                'Halaman ini untuk mengelola <strong>kamar</strong> di tiap lantai — nomor kamar, kapasitas (jumlah penghuni maksimal), serta PIN akses ketua kamar yang dipakai untuk form izin.'
            ),
            Actions\CreateAction::make(),
        ];
    }
}
