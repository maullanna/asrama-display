<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Student;
use App\Models\Vocation;
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
            'vocations' => $this->vocations(),
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
            'vocations' => $this->vocations(),
        ]);
    }

    /**
     * Mahasiswa vokasi (A10) dikelompokkan per lokasi (urutan tetap: Sunter, Karawang).
     * Hanya lokasi yang ada mahasiswanya yang disertakan.
     *
     * @return array<int, array{key:string, label:string, students:\Illuminate\Support\Collection}>
     */
    private function vocations(): array
    {
        $active = Vocation::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $grouped = [];
        foreach (Vocation::LOCATIONS as $key => $label) {
            $list = $active->where('location', $key)->values();
            if ($list->isNotEmpty()) {
                $grouped[] = ['key' => $key, 'label' => $label, 'students' => $list];
            }
        }

        return $grouped;
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

        // Mahasiswa yang sedang isolasi (kondisi 'isolasi' aktif hari ini) -> penghuni ruang isolasi.
        $activeIsolation = fn ($query) => $query->where('type', 'isolasi')
            ->whereDate('start_date', '<=', $today)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today));

        $isolationStudents = Student::query()
            ->where('is_active', true)
            ->whereHas('conditions', $activeIsolation)
            ->with(['conditions' => $activeIsolation])
            ->orderBy('name')
            ->get();

        foreach ($isolationStudents as $student) {
            $student->condition = $student->conditions->first();
            $student->ci_time = null;
            $student->status = 'isolasi';
        }

        $floors = Floor::with([
            'rooms' => fn ($query) => $query->orderBy('sort_order'),
            'rooms.students' => fn ($query) => $query->where('is_active', true)->orderBy('name'),
            'rooms.students.attendanceLogs' => fn ($query) => $query->whereDate('scanned_at', $today)->orderBy('scanned_at'),
            'rooms.students.conditions' => fn ($query) => $query->whereDate('start_date', '<=', $today)
                ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today)),
        ])->orderBy('sort_order')->get();

        foreach ($floors as $floor) {
            foreach ($floor->rooms as $room) {
                // Ruang isolasi: penghuni diambil dari kondisi isolasi (bukan dari room_id).
                if ($room->is_isolation) {
                    $room->setRelation('students', $isolationStudents);
                    $room->occupancy = $isolationStudents->count();
                    $room->students_with_condition = $isolationStudents; // keterangan: nama — detail sakit

                    continue;
                }

                foreach ($room->students as $student) {
                    $condition = $student->conditions->first();
                    $lastCi = $student->attendanceLogs->where('direction', 'ci')->last();

                    $student->condition = $condition;
                    $student->ci_time = $lastCi?->scanned_at;

                    // Status: kondisi (sakit/izin/isolasi) menang; lalu hadir (sudah CI); sisanya belum absen.
                    $student->status = $condition
                        ? $condition->type
                        : ($lastCi ? 'present' : 'absent');
                }

                $room->occupancy = $room->students->where('status', 'present')->count();
                $room->students_with_condition = $room->students->whereNotNull('condition')->values();
            }

            // Tampilkan kamar yang sudah ada mahasiswanya; ruang isolasi selalu tampil.
            $floor->setRelation('rooms', $floor->rooms->filter(
                fn ($room) => $room->is_isolation || $room->students->isNotEmpty()
            )->values());
        }

        // Hanya tampilkan lantai yang punya minimal satu kamar tampil.
        return $floors->filter(fn ($floor) => $floor->rooms->isNotEmpty())->values();
    }
}
