<?php

namespace App\Filament\Resources\StudentConditionResource\Pages;

use App\Filament\Concerns\HasPageInfo;
use App\Filament\Resources\StudentConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentConditions extends ListRecords
{
    use HasPageInfo;

    protected static string $resource = StudentConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->infoAction(
                'Tentang Halaman Izin & Sakit',
                'Halaman ini untuk mencatat status <strong>sakit</strong> atau <strong>izin</strong> mahasiswa beserta rentang tanggalnya. Status ini menentukan tampilan mahasiswa di layar kiosk (TV) — misalnya ditandai sakit atau sedang izin keluar.'
            ),
            Actions\CreateAction::make(),
        ];
    }
}
