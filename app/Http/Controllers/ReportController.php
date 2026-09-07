<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Student;
use App\Models\StudentCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /**
     * Pemetaan pilihan kondisi -> tipe schema + label + arah.
     */
    private const REASONS = [
        'sakit' => ['type' => 'sakit', 'label' => 'Sakit', 'direction' => null],
        'rumah_sakit' => ['type' => 'sakit', 'label' => 'Rumah Sakit', 'direction' => null],
        'training' => ['type' => 'izin', 'label' => 'Training', 'direction' => 'co'],
        'dinas' => ['type' => 'izin', 'label' => 'Dinas luar', 'direction' => 'co'],
    ];

    public function show()
    {
        $rooms = Room::with(['floor', 'students' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('floor_id')->orderBy('sort_order')->get();

        // Data untuk dropdown & filter anggota per kamar (TANPA membocorkan PIN).
        $roomOptions = $rooms->map(fn ($room) => [
            'id' => $room->id,
            'label' => ($room->floor?->name ?? '-').' - Kamar '.$room->room_number,
        ])->values();

        $studentsByRoom = $rooms->mapWithKeys(fn ($room) => [
            $room->id => $room->students->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->student_code,
            ])->values(),
        ]);

        return view('report', [
            'roomOptions' => $roomOptions,
            'studentsByRoom' => $studentsByRoom,
            'reasons' => self::REASONS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'access_pin' => ['required', 'string'],
            'student_id' => ['required', 'exists:students,id'],
            'reason' => ['required', 'in:'.implode(',', array_keys(self::REASONS))],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [], [
            'room_id' => 'kamar',
            'access_pin' => 'PIN kamar',
            'student_id' => 'anggota',
            'reason' => 'kondisi',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal selesai',
        ]);

        $room = Room::findOrFail($data['room_id']);

        // Verifikasi PIN kamar.
        if (blank($room->access_pin) || ! hash_equals((string) $room->access_pin, (string) $data['access_pin'])) {
            return back()
                ->withErrors(['access_pin' => 'PIN kamar salah atau kamar belum punya PIN (hubungi admin).'])
                ->withInput();
        }

        // Pastikan anggota memang penghuni kamar tersebut.
        $student = Student::where('id', $data['student_id'])->where('room_id', $room->id)->first();
        if (! $student) {
            return back()
                ->withErrors(['student_id' => 'Anggota tersebut bukan penghuni kamar yang dipilih.'])
                ->withInput();
        }

        $reason = self::REASONS[$data['reason']];
        $note = $reason['label'];
        if (filled($data['note'] ?? null)) {
            $note .= ' - '.$data['note'];
        }

        $leaderId = $room->students->firstWhere('is_room_leader', true)?->id
            ?? Student::where('room_id', $room->id)->where('is_room_leader', true)->value('id');

        StudentCondition::create([
            'student_id' => $student->id,
            'type' => $reason['type'],
            'direction' => $reason['direction'],
            'start_date' => Carbon::parse($data['start_date']),
            'end_date' => filled($data['end_date'] ?? null) ? Carbon::parse($data['end_date']) : null,
            'note' => $note,
            'reported_by_student_id' => $leaderId,
        ]);

        return redirect()->route('report.show')->with('success', "Laporan tersimpan: {$student->name} ditandai \"{$note}\".");
    }
}
