<?php

namespace App\Filament\Resources\VocationResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\VocationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVocation extends CreateRecord
{
    use RedirectsToIndex;

    protected static string $resource = VocationResource::class;
}
