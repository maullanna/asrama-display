<?php

namespace App\Filament\Resources\IsolasiResource\Pages;

use App\Filament\Concerns\RedirectsToIndex;
use App\Filament\Resources\IsolasiResource;
use App\Models\Room;
use App\Models\StudentCondition;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateIsolasi extends CreateRecord
{
    use RedirectsToIndex;

    protected static string $resource = IsolasiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'isolasi';

        return $data;
    }

    /** Tolak bila ruang isolasi sudah penuh (sesuai kapasitas kamar isolasi, default 2). */
    protected function beforeCreate(): void
    {
        $capacity = (int) (Room::where('is_isolation', true)->value('capacity') ?? 2);

        $active = StudentCondition::where('type', 'isolasi')
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->count();

        if ($active >= $capacity) {
            Notification::make()
                ->title('Ruang isolasi penuh')
                ->body("Kapasitas isolasi ({$capacity}) sudah penuh. Keluarkan mahasiswa lain dulu sebelum menambah.")
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
