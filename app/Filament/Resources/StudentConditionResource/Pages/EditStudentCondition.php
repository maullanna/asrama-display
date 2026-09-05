<?php

namespace App\Filament\Resources\StudentConditionResource\Pages;

use App\Filament\Resources\StudentConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentCondition extends EditRecord
{
    protected static string $resource = StudentConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
