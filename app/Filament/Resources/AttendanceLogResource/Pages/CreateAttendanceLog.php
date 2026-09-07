<?php

namespace App\Filament\Resources\AttendanceLogResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\AttendanceLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendanceLog extends CreateRecord
{
    use RedirectsToIndex;

    protected static string $resource = AttendanceLogResource::class;
}
