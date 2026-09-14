<?php

namespace App\Filament\Resources\VocationResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\VocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVocation extends EditRecord
{
    use RedirectsToIndex;

    protected static string $resource = VocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
