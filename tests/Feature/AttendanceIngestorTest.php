<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use App\Services\AttendanceIngestor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceIngestorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_parses_attlog_and_links_pin_to_student(): void
    {
        $floor = Floor::create(['name' => 'Lantai 1', 'slug' => 'lantai-1', 'sort_order' => 1]);
        $room = Room::create(['floor_id' => $floor->id, 'room_number' => '101', 'capacity' => 8, 'sort_order' => 1]);
        $student = Student::create([
            'room_id' => $room->id,
            'student_code' => '3265359',
            'device_pin' => '3265359',
            'name' => 'Varel Maulana',
            'is_room_leader' => true,
        ]);

        $body = "3265359\t2026-09-07 09:23:22\t0\t1\t0\t0\n"
              ."250204\t2026-09-07 09:30:57\t1\t1\t0\t0\n";

        $count = app(AttendanceIngestor::class)->ingestAttlog($body, 'NHZ4234600128');

        $this->assertSame(2, $count);

        // Baris 1: PIN cocok Varel, status 0 -> masuk (ci)
        $this->assertDatabaseHas('attendance_logs', [
            'device_pin' => '3265359',
            'student_id' => $student->id,
            'direction' => 'ci',
            'device_sn' => 'NHZ4234600128',
            'method' => 'fingerprint',
        ]);

        // Baris 2: PIN belum terdaftar -> student_id null, status 1 -> keluar (co)
        $this->assertDatabaseHas('attendance_logs', [
            'device_pin' => '250204',
            'student_id' => null,
            'direction' => 'co',
        ]);
    }

    public function test_it_does_not_duplicate_on_resend(): void
    {
        $body = "3265359\t2026-09-07 09:23:22\t0\t1\t0\t0\n";

        $ingestor = app(AttendanceIngestor::class);
        $ingestor->ingestAttlog($body);
        $ingestor->ingestAttlog($body); // mesin kirim ulang data yang sama

        $this->assertSame(1, AttendanceLog::where('device_pin', '3265359')->count());
    }
}
