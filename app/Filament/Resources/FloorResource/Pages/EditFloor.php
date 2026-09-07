<?php

namespace App\Filament\Resources\FloorResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\FloorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFloor extends EditRecord
{
    use RedirectsToIndex;

    protected static string $resource = FloorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
