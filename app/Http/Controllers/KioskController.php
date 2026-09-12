<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KioskController extends Controller
{
    public function index()
    {
        // QR menuju form laporan ketua kamar.
        $reportQr = QrCode::format('svg')->size(96)->margin(0)->errorCorrection('M')->generate(route('report.show'));

        $floors = $this->buildFloors();

        return view('kiosk', [
            'floors' => $floors,
            'today' => Carbon::today(),
            'reportQr' => $reportQr,
            'abnormal' => $this->abnormalList($floors),
            'isolation' => $this->isolationRoom(),
        ]);
    }

    /**
     * Endpoint untuk polling AJAX: render ulang kartu kamar saja (tanpa layout).
     */
    public function rooms()
    {
        $floors = $this->buildFloors();

        return view('partials.kiosk-rooms', [
            'floors' => $floors,
            'abnormal' => $this->abnormalList($floors),
            'isolation' => $this->isolationRoom(),
        ]);
    }

    /**
     * Ruang isolasi + penghuninya (mahasiswa dengan kondisi 'isolasi' aktif hari ini).
     * Mengembalikan objek Room ber-relasi students (yang diisolasi), atau null bila
     * belum ada kamar yang ditandai sebagai ruang isolasi.
     */
    private function isolationRoom(): ?Room
    {
        $room = Room::where('is_isolation', true)->with('floor')->first();
        if (! $room) {
            return null;
        }

        $today = Carbon::today();
        $activeIsolation = fn ($query) => $query->where('type', 'isolasi')
            ->whereDate('start_date', '<=', $today)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today));

        $students = Student::query()
            ->where('is_active', true)
            ->whereHas('conditions', $activeIsolation)
            ->with(['conditions' => $activeIsolation])
            ->orderBy('name')
            ->get();

        foreach ($students as $student) {
            $student->condition = $student->conditions->first();
            $student->ci_time = null;
            $student->status = 'isolasi';
        }

        $room->setRelation('students', $students);
        $room->occupancy = $students->count();
        $room->students_with_condition = $students;

        return $room;
    }

    /**
     * Daftar mahasiswa "abnormal": setelah jam batas CI, masih berstatus 'absent'
     * (belum CI hari ini & tanpa keterangan sakit/izin). Sebelum jam batas => kosong.
     *
     * @return array<int, array{name:string, code:string, room:string}>
     */
    private function abnormalList(Collection $floors): array
    {
        $cutoff = Carbon::today()->setTimeFromTimeString(config('asrama.ci_cutoff', '21:00'));
        if (Carbon::now()->lt($cutoff)) {
            return [];
        }

        $abnormal = [];
        foreach ($floors as $floor) {
            foreach ($floor->rooms as $room) {
                foreach ($room->students as $student) {
                    if ($student->status === 'absent') {
                        $abnormal[] = [
                            'name' => $student->name,
                            'code' => $student->student_code,
                            'room' => str_replace('Lantai', 'Floor', $floor->name).' - '.$room->room_number,
                        ];
                    }
                }
            }
        }

        return $abnormal;
    }

    /**
     * Bangun daftar lantai -> kamar -> penghuni/kondisi untuk hari ini.
     * Hanya menyertakan kamar yang ada mahasiswanya dan lantai yang ada kamarnya.
     */
    private function buildFloors(): Collection
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

                    // Status: kondisi (sakit/izin) menang; lalu hadir (sudah CI); sisanya belum absen.
                    $student->status = $condition
                        ? $condition->type              // 'sakit' | 'izin'
                        : ($lastCi ? 'present' : 'absent');
                }

                $room->occupancy = $room->students->where('status', 'present')->count();
                $room->students_with_condition = $room->students->whereNotNull('condition')->values();
            }

            // Hanya tampilkan kamar yang sudah ada mahasiswanya.
            $floor->setRelation('rooms', $floor->rooms->filter(
                fn ($room) => $room->students->isNotEmpty()
            )->values());
        }

        // Hanya tampilkan lantai yang punya minimal satu kamar berisi mahasiswa.
        return $floors->filter(fn ($floor) => $floor->rooms->isNotEmpty())->values();
    }
}
