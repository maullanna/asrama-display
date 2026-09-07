<?php

namespace App\Filament\Resources\StudentConditionResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\StudentConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentCondition extends CreateRecord
{
    use RedirectsToIndex;

    protected static string $resource = StudentConditionResource::class;
}
