<?php

namespace App\Filament\Resources\StudentConditionResource\Pages;

use App\Filament\Resources\StudentConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentConditions extends ListRecords
{
    protected static string $resource = StudentConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
