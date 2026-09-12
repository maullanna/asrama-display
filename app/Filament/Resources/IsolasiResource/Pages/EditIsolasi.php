<?php

namespace App\Filament\Resources\IsolasiResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\IsolasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIsolasi extends EditRecord
{
    use RedirectsToIndex;

    protected static string $resource = IsolasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
