<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    use RedirectsToIndex;

    protected static string $resource = StudentResource::class;
}
