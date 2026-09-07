<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\RoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoom extends EditRecord
{
    use RedirectsToIndex;

    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
