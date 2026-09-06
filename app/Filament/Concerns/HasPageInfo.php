<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

trait HasPageInfo
{
    protected function infoAction(string $heading, string $content): Action
    {
        return Action::make('info')
            ->label('Info')
            ->icon('heroicon-o-information-circle')
            ->color('gray')
            ->modalHeading($heading)
            ->modalIcon('heroicon-o-information-circle')
            ->modalContent(new HtmlString(
                '<div class="text-sm leading-6 text-gray-600 dark:text-gray-300">'.$content.'</div>'
            ))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup');
    }
}
