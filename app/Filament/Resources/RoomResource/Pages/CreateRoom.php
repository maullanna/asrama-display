<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\RoomResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRoom extends CreateRecord
{
    use RedirectsToIndex;

    protected static string $resource = RoomResource::class;
}
