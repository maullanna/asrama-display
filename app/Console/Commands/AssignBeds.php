<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;

class AssignBeds extends Command
{
    protected $signature = 'beds:assign {--only-empty : Hanya isi yang belum punya nomor kasur}';

    protected $description = 'Isi nomor kasur mahasiswa per kamar berurutan (format: Bed {kamar}.{urutan})';

    public function handle(): int
    {
        // Kamar non-isolasi yang punya nomor kamar; urutan mahasiswa mengikuti tampilan kiosk (nama).
        $rooms = Room::where('is_isolation', false)
            ->with(['students' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->get();

        $count = 0;

        foreach ($rooms as $room) {
            $i = 0;
            foreach ($room->students as $student) {
                $i++;

                if ($this->option('only-empty') && ! empty($student->bed_number)) {
                    continue;
                }

                $student->bed_number = "Bed {$room->room_number}.{$i}";
                $student->saveQuietly();   // tanpa memicu event (tak perlu regen thumbnail)
                $count++;
            }
        }

        $this->info("Nomor kasur diisi untuk {$count} mahasiswa.");

        return self::SUCCESS;
    }
}
