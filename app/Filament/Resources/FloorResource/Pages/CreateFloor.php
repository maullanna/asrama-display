<?php

namespace App\Filament\Resources\FloorResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\FloorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFloor extends CreateRecord
{
    use RedirectsToIndex;

    protected static string $resource = FloorResource::class;
}
