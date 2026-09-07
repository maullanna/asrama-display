<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use Illuminate\Support\Carbon;

class KioskController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $floors = Floor::with([
            'rooms' => fn ($query) => $query->orderBy('sort_order'),
            'rooms.students' => fn ($query) => $query->where('is_active', true)->orderBy('name'),
            'rooms.students.attendanceLogs' => fn ($query) => $query->whereDate('scanned_at', $today)->orderBy('scanned_at'),
            'rooms.students.conditions' => fn ($query) => $query->whereDate('start_date', '<=', $today)
                ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today)),
        ])->orderBy('sort_order')->get();

        foreach ($floors as $floor) {
            foreach ($floor->rooms as $room) {
                foreach ($room->students as $student) {
                    $condition = $student->conditions->first();
                    $lastCi = $student->attendanceLogs->where('direction', 'ci')->last();

                    $student->condition = $condition;
                    $student->ci_time = $lastCi?->scanned_at;
                    $student->is_occupying = (! $condition) && $lastCi !== null;
                }

                $room->occupants = $room->students->where('is_occupying', true)->values();
                $room->occupancy = $room->occupants->count();
                $room->students_with_condition = $room->students->whereNotNull('condition')->values();
            }

            // Hanya tampilkan kamar yang sudah ada mahasiswanya.
            $floor->setRelation('rooms', $floor->rooms->filter(
                fn ($room) => $room->students->isNotEmpty()
            )->values());
        }

        // Hanya tampilkan lantai yang punya minimal satu kamar berisi mahasiswa.
        $floors = $floors->filter(fn ($floor) => $floor->rooms->isNotEmpty())->values();

        return view('kiosk', [
            'floors' => $floors,
            'today' => $today,
        ]);
    }
}
