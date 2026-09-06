<?php

namespace App\Filament\Resources\AttendanceLogResource\Pages;

use App\Filament\Concerns\HasPageInfo;
use App\Filament\Resources\AttendanceLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceLogs extends ListRecords
{
    use HasPageInfo;

    protected static string $resource = AttendanceLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->infoAction(
                'Tentang Halaman Log Absensi',
                'Halaman ini menampilkan catatan mentah hasil scan mesin fingerprint (<strong>check-in / check-out</strong>). Umumnya terisi otomatis dari mesin, jadi jarang perlu diisi manual.'
            ),
            Actions\CreateAction::make(),
        ];
    }
}
